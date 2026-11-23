<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('shared.notifications', [
            'notifications' => $request->user()->notifications()->paginate(20),
        ]);
    }

    /** Tandai dibaca lalu buka halaman terkait. Hanya notifikasi milik pengguna sendiri. */
    public function open(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        // URL disimpan sebagai path relatif; tolak apa pun yang bukan path internal (cegah open redirect).
        if (! is_string($url) || ! str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return redirect()->route('notifications.index');
        }

        return redirect()->to($url);
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
