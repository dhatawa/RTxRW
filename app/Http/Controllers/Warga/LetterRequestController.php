<?php

namespace App\Http\Controllers\Warga;

use App\Enums\RequestStatus;
use App\Enums\WorkflowAction;
use App\Exceptions\WorkflowException;
use App\Http\Controllers\Controller;
use App\Models\LetterRequest;
use App\Models\LetterType;
use App\Models\Resident;
use App\Services\ActivityLogger;
use App\Services\DocumentStorage;
use App\Services\LetterWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LetterRequestController extends Controller
{
    public function __construct(
        private LetterWorkflow $workflow,
        private DocumentStorage $documents,
    ) {}

    public function index(Request $request): View
    {
        $resident = $this->resident($request);
        $filter = $request->query('status');

        $query = $resident->letterRequests()->with('letterType')->latest('updated_at');

        match ($filter) {
            'proses' => $query->status(RequestStatus::inProgress()),
            'perbaikan' => $query->status([RequestStatus::RevisionRequired, RequestStatus::Draft]),
            'selesai' => $query->status(RequestStatus::done()),
            'ditolak' => $query->status([RequestStatus::RejectedByRt, RequestStatus::RejectedByRw]),
            default => null,
        };

        return view('warga.requests.index', [
            'requests' => $query->paginate(10)->withQueryString(),
            'filter' => $filter,
        ]);
    }

    public function create(Request $request): View
    {
        return view('warga.requests.create', [
            'resident' => $this->resident($request),
            'letterTypes' => LetterType::active()->ordered()->withCount('requirements')->get(),
        ]);
    }

    public function form(Request $request, LetterType $letterType): View|RedirectResponse
    {
        abort_unless($letterType->is_active, 404);

        $resident = $this->resident($request);

        if (! $resident->canSubmitLetters()) {
            return redirect()->route('warga.requests.create');
        }

        return view('warga.requests.form', [
            'type' => $letterType->load('requirements'),
            'resident' => $resident,
            'letterRequest' => null,
        ]);
    }

    public function store(Request $request, LetterType $letterType): RedirectResponse
    {
        abort_unless($letterType->is_active, 404);
        $resident = $this->resident($request);

        if (! $resident->canSubmitLetters()) {
            return redirect()->route('warga.requests.create')
                ->with('error', 'Lengkapi profil dan tunggu verifikasi Ketua RT sebelum mengajukan surat.');
        }

        $letterType->load('requirements');
        $data = $request->validate(...$this->rules($letterType, null, $request->input('intent')));

        $letterRequest = DB::transaction(function () use ($request, $resident, $letterType, $data) {
            $letterRequest = LetterRequest::create([
                'resident_id' => $resident->id,
                'rt_id' => $resident->rt_id,
                'letter_type_id' => $letterType->id,
                'purpose' => $data['purpose'],
                'form_data' => $this->formData($letterType, $data),
            ]);

            $this->storeDocuments($request, $letterRequest, $letterType);

            ActivityLogger::log('request.created', "Draf {$letterType->name} dibuat ({$letterRequest->reference_number})", $letterRequest);

            return $letterRequest;
        });

        return $this->afterSave($request, $letterRequest, 'Draf pengajuan tersimpan.');
    }

    public function show(Request $request, LetterRequest $letterRequest): View
    {
        Gate::authorize('view', $letterRequest);

        $letterRequest->load(['letterType.requirements', 'documents.requirement', 'histories.actor', 'issuedLetter', 'rt']);

        return view('warga.requests.show', [
            'letterRequest' => $letterRequest,
            'canSubmit' => $this->workflow->can($letterRequest, WorkflowAction::Submit, $request->user())
                || $this->workflow->can($letterRequest, WorkflowAction::Resubmit, $request->user()),
        ]);
    }

    public function edit(Request $request, LetterRequest $letterRequest): View
    {
        Gate::authorize('update', $letterRequest);

        return view('warga.requests.form', [
            'type' => $letterRequest->letterType->load('requirements'),
            'resident' => $this->resident($request),
            'letterRequest' => $letterRequest->load('documents'),
        ]);
    }

    public function update(Request $request, LetterRequest $letterRequest): RedirectResponse
    {
        Gate::authorize('update', $letterRequest);

        $type = $letterRequest->letterType->load('requirements');
        $data = $request->validate(...$this->rules($type, $letterRequest, $request->input('intent')));

        DB::transaction(function () use ($request, $letterRequest, $type, $data) {
            $letterRequest->update([
                'purpose' => $data['purpose'],
                'form_data' => $this->formData($type, $data),
            ]);

            $this->storeDocuments($request, $letterRequest, $type);

            ActivityLogger::log('request.updated', "Pengajuan {$letterRequest->reference_number} diperbarui", $letterRequest);
        });

        return $this->afterSave($request, $letterRequest, 'Perubahan tersimpan.');
    }

    public function submit(Request $request, LetterRequest $letterRequest): RedirectResponse
    {
        Gate::authorize('update', $letterRequest);

        $action = $letterRequest->status === RequestStatus::RevisionRequired ? WorkflowAction::Resubmit : WorkflowAction::Submit;
        $this->workflow->apply($letterRequest, $action, $request->user());

        return redirect()->route('warga.requests.show', $letterRequest)
            ->with('success', 'Pengajuan terkirim ke Ketua RT '.$letterRequest->rt->number.'. Anda akan menerima notifikasi setiap ada perubahan status.');
    }

    public function destroy(Request $request, LetterRequest $letterRequest): RedirectResponse
    {
        Gate::authorize('delete', $letterRequest);

        DB::transaction(function () use ($letterRequest) {
            foreach ($letterRequest->documents as $document) {
                $this->documents->delete($document);
            }
            ActivityLogger::log('request.deleted', "Draf {$letterRequest->reference_number} dihapus", $letterRequest);
            $letterRequest->delete();
        });

        return redirect()->route('warga.requests.index')->with('success', 'Draf dihapus.');
    }

    private function afterSave(Request $request, LetterRequest $letterRequest, string $message): RedirectResponse
    {
        if ($request->input('intent') !== 'submit') {
            return redirect()->route('warga.requests.show', $letterRequest)->with('success', $message);
        }

        $action = $letterRequest->status === RequestStatus::RevisionRequired ? WorkflowAction::Resubmit : WorkflowAction::Submit;

        try {
            $this->workflow->apply($letterRequest->fresh(), $action, $request->user());
        } catch (WorkflowException $e) {
            return redirect()->route('warga.requests.edit', $letterRequest)
                ->with('error', 'Data tersimpan sebagai draf, tetapi belum bisa dikirim: '.$e->getMessage());
        }

        return redirect()->route('warga.requests.show', $letterRequest)
            ->with('success', 'Pengajuan terkirim ke Ketua RT '.$letterRequest->rt->number.'. Anda akan menerima notifikasi setiap ada perubahan status.');
    }

    /** @return array{0: array, 1: array, 2: array} */
    private function rules(LetterType $type, ?LetterRequest $existing, ?string $intent): array
    {
        $rules = ['purpose' => ['required', 'string', 'min:5', 'max:500'], 'intent' => ['nullable', Rule::in(['draft', 'submit'])]];
        $attributes = ['purpose' => 'keperluan'];

        foreach ($type->fieldDefinitions() as $field) {
            $key = 'fields.'.$field['key'];
            $rules[$key] = array_merge(
                [$field['required'] ? 'required' : 'nullable'],
                match ($field['type']) {
                    'date' => ['date'],
                    'number' => ['numeric', 'min:0'],
                    'textarea' => ['string', 'max:1000'],
                    default => ['string', 'max:200'],
                },
            );
            $attributes[$key] = strtolower($field['label']);
        }

        $uploaded = $existing?->documents->pluck('letter_requirement_id')->all() ?? [];

        foreach ($type->requirements as $requirement) {
            $key = 'documents.'.$requirement->id;
            // Dokumen wajib hanya dituntut saat dikirim dan belum pernah diunggah; draf boleh belum lengkap.
            $required = $intent === 'submit' && $requirement->is_required && ! in_array($requirement->id, $uploaded);
            $rules[$key] = DocumentStorage::rules($required);
            $attributes[$key] = $requirement->name;
        }

        $messages = [
            'documents.*.max' => ':attribute maksimal 2 MB.',
            'documents.*.mimes' => ':attribute harus berupa JPG, PNG, atau PDF.',
            'documents.*.mimetypes' => ':attribute harus berupa JPG, PNG, atau PDF.',
        ];

        return [$rules, $messages, $attributes];
    }

    private function formData(LetterType $type, array $data): array
    {
        $values = [];
        foreach ($type->fieldDefinitions() as $field) {
            $values[$field['key']] = $data['fields'][$field['key']] ?? null;
        }

        return $values;
    }

    private function storeDocuments(Request $request, LetterRequest $letterRequest, LetterType $type): void
    {
        foreach ($type->requirements as $requirement) {
            if ($file = $request->file('documents.'.$requirement->id)) {
                $this->documents->store($letterRequest, $requirement, $file, $request->user());
            }
        }
    }

    private function resident(Request $request): Resident
    {
        return $request->user()->resident()->with('rt')->firstOrFail();
    }
}
