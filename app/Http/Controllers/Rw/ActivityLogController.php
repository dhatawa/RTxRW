<?php

namespace App\Http\Controllers\Rw;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public const GROUPS = [
        'workflow' => 'Keputusan pengajuan',
        'resident' => 'Data warga',
        'request' => 'Pengajuan (warga)',
        'letter' => 'Unduhan surat',
        'letter_type' => 'Jenis surat',
        'template' => 'Template',
        'rt' => 'Data RT',
        'rt_head' => 'Akun Ketua RT',
        'auth' => 'Login',
        'account' => 'Akun',
        'report' => 'Laporan',
        'settings' => 'Pengaturan',
    ];

    public function index(Request $request): View
    {
        $group = $request->query('jenis');
        $search = trim((string) $request->query('q'));

        $logs = ActivityLog::query()->with('user')
            ->when(array_key_exists((string) $group, self::GROUPS), fn ($q) => $q->where('action', 'like', $group.'.%'))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->whereLike('description', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->whereLike('name', "%{$search}%"))))
            ->when($request->date('tanggal'), fn ($q, $d) => $q->whereDate('created_at', $d))
            ->latest('created_at')->latest('id')
            ->paginate(30)->withQueryString();

        return view('rw.activity', [
            'logs' => $logs,
            'groups' => self::GROUPS,
            'group' => $group,
            'search' => $search,
        ]);
    }
}
