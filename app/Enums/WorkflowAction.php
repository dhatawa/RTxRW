<?php

namespace App\Enums;

enum WorkflowAction: string
{
    case Submit = 'submitted';
    case Resubmit = 'resubmitted';
    case RtApprove = 'rt_approved';
    case RtReject = 'rt_rejected';
    case RtRevision = 'rt_revision';
    case RwApprove = 'rw_approved';
    case RwReject = 'rw_rejected';
    case RwRevision = 'rw_revision';
    case Issue = 'issued';
    case Complete = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Submit => 'Pengajuan dikirim',
            self::Resubmit => 'Perbaikan dikirim ulang',
            self::RtApprove => 'Disetujui Ketua RT, diteruskan ke RW',
            self::RtReject => 'Ditolak Ketua RT',
            self::RtRevision => 'Ketua RT meminta perbaikan',
            self::RwApprove => 'Disetujui Ketua RW',
            self::RwReject => 'Ditolak Ketua RW',
            self::RwRevision => 'Ketua RW meminta perbaikan',
            self::Issue => 'Surat diterbitkan',
            self::Complete => 'Surat diunduh warga',
        };
    }

    public function requiresNote(): bool
    {
        return in_array($this, [
            self::RtReject, self::RtRevision, self::RwReject, self::RwRevision,
        ], true);
    }

    /**
     * Peta transisi yang sah: aksi => [status asal yang diizinkan, status tujuan, role pelaku].
     *
     * @return array{from: list<RequestStatus>, to: RequestStatus, role: Role}
     */
    public function rule(): array
    {
        return match ($this) {
            self::Submit => ['from' => [RequestStatus::Draft], 'to' => RequestStatus::PendingRt, 'role' => Role::Warga],
            self::Resubmit => ['from' => [RequestStatus::RevisionRequired], 'to' => RequestStatus::PendingRt, 'role' => Role::Warga],
            self::RtApprove => ['from' => [RequestStatus::PendingRt], 'to' => RequestStatus::PendingRw, 'role' => Role::KetuaRt],
            self::RtReject => ['from' => [RequestStatus::PendingRt], 'to' => RequestStatus::RejectedByRt, 'role' => Role::KetuaRt],
            self::RtRevision => ['from' => [RequestStatus::PendingRt], 'to' => RequestStatus::RevisionRequired, 'role' => Role::KetuaRt],
            self::RwApprove => ['from' => [RequestStatus::PendingRw], 'to' => RequestStatus::Approved, 'role' => Role::KetuaRw],
            self::RwReject => ['from' => [RequestStatus::PendingRw], 'to' => RequestStatus::RejectedByRw, 'role' => Role::KetuaRw],
            self::RwRevision => ['from' => [RequestStatus::PendingRw], 'to' => RequestStatus::RevisionRequired, 'role' => Role::KetuaRw],
            self::Issue => ['from' => [RequestStatus::Approved], 'to' => RequestStatus::Issued, 'role' => Role::KetuaRw],
            self::Complete => ['from' => [RequestStatus::Issued], 'to' => RequestStatus::Completed, 'role' => Role::Warga],
        };
    }
}
