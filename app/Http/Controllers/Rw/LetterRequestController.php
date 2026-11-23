<?php

namespace App\Http\Controllers\Rw;

use App\Enums\RequestStatus;
use App\Enums\WorkflowAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\DecisionRequest;
use App\Models\LetterRequest;
use App\Models\LetterType;
use App\Models\Rt;
use App\Services\LetterIssuer;
use App\Services\LetterWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LetterRequestController extends Controller
{
    public function __construct(private LetterWorkflow $workflow) {}

    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'perlu-tindakan');
        $search = trim((string) $request->query('q'));

        $query = LetterRequest::query()->visibleToRw()
            ->with(['letterType', 'resident', 'rt'])
            ->when($request->query('rt'), fn ($q, $id) => $q->where('rt_id', $id))
            ->when($request->query('jenis'), fn ($q, $id) => $q->where('letter_type_id', $id))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->whereLike('reference_number', "%{$search}%")
                ->orWhereHas('resident', fn ($r) => $r->whereLike('full_name', "%{$search}%"))));

        match ($tab) {
            'siap-terbit' => $query->status(RequestStatus::Approved)->oldest('rw_decided_at'),
            'di-rt' => $query->status([RequestStatus::PendingRt, RequestStatus::RevisionRequired])->latest('submitted_at'),
            'selesai' => $query->status(RequestStatus::done())->latest('issued_at'),
            'ditolak' => $query->status([RequestStatus::RejectedByRt, RequestStatus::RejectedByRw])->latest('updated_at'),
            'semua' => $query->latest('submitted_at'),
            default => $query->status(RequestStatus::PendingRw)->oldest('rt_decided_at'),
        };

        return view('rw.requests.index', [
            'requests' => $query->paginate(15)->withQueryString(),
            'tab' => $tab,
            'search' => $search,
            'rts' => Rt::query()->orderBy('number')->get(['id', 'number']),
            'letterTypes' => LetterType::ordered()->get(['id', 'name']),
            'counts' => [
                'perlu-tindakan' => LetterRequest::query()->status(RequestStatus::PendingRw)->count(),
                'siap-terbit' => LetterRequest::query()->status(RequestStatus::Approved)->count(),
            ],
        ]);
    }

    public function show(Request $request, LetterRequest $letterRequest): View
    {
        Gate::authorize('view', $letterRequest);

        $letterRequest->load(['letterType.requirements', 'letterType.activeTemplate', 'resident.user', 'resident.verifier', 'documents.requirement', 'histories.actor', 'issuedLetter.issuer', 'rt.ketua']);

        $user = $request->user();

        return view('rw.requests.show', [
            'letterRequest' => $letterRequest,
            'canDecide' => $this->workflow->can($letterRequest, WorkflowAction::RwApprove, $user),
            'canIssue' => $this->workflow->can($letterRequest, WorkflowAction::Issue, $user),
        ]);
    }

    public function decide(DecisionRequest $request, LetterRequest $letterRequest): RedirectResponse
    {
        Gate::authorize('decideAsRw', $letterRequest);

        $action = match ($request->validated('decision')) {
            'approve' => WorkflowAction::RwApprove,
            'reject' => WorkflowAction::RwReject,
            'revision' => WorkflowAction::RwRevision,
        };

        $this->workflow->apply($letterRequest, $action, $request->user(), $request->validated('note'));

        $message = match ($action) {
            WorkflowAction::RwApprove => "Pengajuan {$letterRequest->reference_number} disetujui. Surat siap diterbitkan.",
            WorkflowAction::RwReject => "Pengajuan {$letterRequest->reference_number} ditolak.",
            default => "Permintaan perbaikan untuk {$letterRequest->reference_number} sudah dikirim ke warga. Setelah diperbaiki, pengajuan akan diverifikasi ulang oleh Ketua RT.",
        };

        return redirect()->route('rw.requests.show', $letterRequest)->with('success', $message);
    }

    public function issue(Request $request, LetterRequest $letterRequest, LetterIssuer $issuer): RedirectResponse
    {
        Gate::authorize('decideAsRw', $letterRequest);

        $letter = $issuer->issue($letterRequest, $request->user());

        return redirect()->route('rw.requests.show', $letterRequest)
            ->with('success', "Surat terbit dengan nomor {$letter->letter_number}. Warga sudah diberi tahu.");
    }
}
