<?php

namespace App\Http\Controllers\Rt;

use App\Enums\RequestStatus;
use App\Enums\WorkflowAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\DecisionRequest;
use App\Models\LetterRequest;
use App\Models\LetterType;
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
        $rt = $request->user()->managedRt;
        $tab = $request->query('tab', 'perlu-tindakan');
        $search = trim((string) $request->query('q'));

        $query = LetterRequest::query()->forRt($rt->id)->visibleToRw()
            ->with(['letterType', 'resident'])
            ->when($request->query('jenis'), fn ($q, $id) => $q->where('letter_type_id', $id))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->whereLike('reference_number', "%{$search}%")
                ->orWhereHas('resident', fn ($r) => $r->whereLike('full_name', "%{$search}%"))));

        match ($tab) {
            'di-rw' => $query->status([RequestStatus::PendingRw, RequestStatus::Approved])->latest('rt_decided_at'),
            'perbaikan' => $query->status(RequestStatus::RevisionRequired)->latest('rt_decided_at'),
            'selesai' => $query->status(RequestStatus::done())->latest('issued_at'),
            'ditolak' => $query->status([RequestStatus::RejectedByRt, RequestStatus::RejectedByRw])->latest('updated_at'),
            'semua' => $query->latest('submitted_at'),
            default => $query->status(RequestStatus::PendingRt)->oldest('submitted_at'),
        };

        return view('rt.requests.index', [
            'requests' => $query->paginate(15)->withQueryString(),
            'tab' => $tab,
            'search' => $search,
            'letterTypes' => LetterType::ordered()->get(['id', 'name']),
            'pendingCount' => LetterRequest::query()->forRt($rt->id)->status(RequestStatus::PendingRt)->count(),
        ]);
    }

    public function show(Request $request, LetterRequest $letterRequest): View
    {
        Gate::authorize('view', $letterRequest);
        Gate::authorize('decideAsRt', $letterRequest);

        $letterRequest->load(['letterType.requirements', 'resident.user', 'resident.verifier', 'documents.requirement', 'histories.actor', 'issuedLetter', 'rt']);

        return view('rt.requests.show', [
            'letterRequest' => $letterRequest,
            'canDecide' => $this->workflow->can($letterRequest, WorkflowAction::RtApprove, $request->user()),
        ]);
    }

    public function decide(DecisionRequest $request, LetterRequest $letterRequest): RedirectResponse
    {
        Gate::authorize('decideAsRt', $letterRequest);

        $action = match ($request->validated('decision')) {
            'approve' => WorkflowAction::RtApprove,
            'reject' => WorkflowAction::RtReject,
            'revision' => WorkflowAction::RtRevision,
        };

        $this->workflow->apply($letterRequest, $action, $request->user(), $request->validated('note'));

        $message = match ($action) {
            WorkflowAction::RtApprove => "Pengajuan {$letterRequest->reference_number} disetujui dan diteruskan ke Ketua RW.",
            WorkflowAction::RtReject => "Pengajuan {$letterRequest->reference_number} ditolak. Warga sudah diberi tahu alasannya.",
            default => "Permintaan perbaikan untuk {$letterRequest->reference_number} sudah dikirim ke warga.",
        };

        $next = LetterRequest::query()->forRt($letterRequest->rt_id)->status(RequestStatus::PendingRt)->oldest('submitted_at')->first();

        return redirect()->route($next ? 'rt.requests.show' : 'rt.requests.index', $next ?? [])->with('success', $message);
    }
}
