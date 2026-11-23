<?php

namespace App\Http\Controllers\Shared;

use App\Enums\RequestStatus;
use App\Enums\WorkflowAction;
use App\Http\Controllers\Controller;
use App\Models\LetterRequest;
use App\Services\ActivityLogger;
use App\Services\LetterWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LetterDownloadController extends Controller
{
    public function download(Request $request, LetterRequest $letterRequest, LetterWorkflow $workflow): StreamedResponse
    {
        Gate::authorize('downloadLetter', $letterRequest);

        $letter = $letterRequest->issuedLetter;
        abort_unless($letter && Storage::disk('local')->exists($letter->file_path), 404);

        $user = $request->user();

        // Unduhan pertama oleh warga menandai pengajuan selesai.
        if ($user->isWarga() && $letterRequest->status === RequestStatus::Issued) {
            $letter->forceFill(['first_downloaded_at' => now()])->save();
            $workflow->apply($letterRequest, WorkflowAction::Complete, $user);
        } else {
            ActivityLogger::log('letter.downloaded', "Surat {$letter->letter_number} diunduh", $letterRequest);
        }

        return Storage::disk('local')->download($letter->file_path, $letter->downloadFilename(), [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
