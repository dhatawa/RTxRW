<?php

namespace App\Http\Controllers\Rw;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\IssuedLetter;
use App\Models\LetterRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IssuanceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        return view('rw.issuance', [
            'ready' => LetterRequest::query()->status(RequestStatus::Approved)
                ->with(['letterType.activeTemplate', 'resident', 'rt'])->oldest('rw_decided_at')->get(),
            'issued' => IssuedLetter::query()
                ->with(['letterRequest.letterType', 'letterRequest.resident', 'letterRequest.rt', 'issuer'])
                ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->whereLike('letter_number', "%{$search}%")
                    ->orWhere('verification_code', $search)
                    ->orWhereHas('letterRequest.resident', fn ($r) => $r->whereLike('full_name', "%{$search}%"))))
                ->latest('issued_at')->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }
}
