<?php

namespace App\Models;

use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Enums\WorkflowAction;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/** Riwayat keputusan bersifat append-only: tidak bisa diubah atau dihapus lewat model. */
#[Fillable(['letter_request_id', 'actor_id', 'actor_role', 'action', 'from_status', 'to_status', 'note'])]
class ApprovalHistory extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'actor_role' => Role::class,
            'action' => WorkflowAction::class,
            'from_status' => RequestStatus::class,
            'to_status' => RequestStatus::class,
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Riwayat persetujuan tidak boleh diubah.'));
        static::deleting(fn () => throw new LogicException('Riwayat persetujuan tidak boleh dihapus.'));
    }

    public function letterRequest(): BelongsTo
    {
        return $this->belongsTo(LetterRequest::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }
}
