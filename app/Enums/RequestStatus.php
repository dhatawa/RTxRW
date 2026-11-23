<?php

namespace App\Enums;

/**
 * Status pengajuan surat.
 *
 * "Submitted" tidak disimpan sebagai status yang menetap: saat warga mengirim,
 * peristiwa "submitted" dicatat di riwayat dan status langsung menjadi PendingRt.
 * Case Submitted tetap ada agar data lama/ekspor tetap terbaca.
 */
enum RequestStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case PendingRt = 'pending_rt';
    case RevisionRequired = 'revision_required';
    case RejectedByRt = 'rejected_rt';
    case PendingRw = 'pending_rw';
    case RejectedByRw = 'rejected_rw';
    case Approved = 'approved';
    case Issued = 'issued';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Terkirim',
            self::PendingRt => 'Menunggu Ketua RT',
            self::RevisionRequired => 'Perlu Perbaikan',
            self::RejectedByRt => 'Ditolak Ketua RT',
            self::PendingRw => 'Menunggu Ketua RW',
            self::RejectedByRw => 'Ditolak Ketua RW',
            self::Approved => 'Disetujui, menunggu terbit',
            self::Issued => 'Surat terbit',
            self::Completed => 'Selesai',
        };
    }

    /** Kelompok warna untuk komponen badge. */
    public function tone(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Submitted, self::PendingRt, self::PendingRw => 'info',
            self::RevisionRequired => 'warning',
            self::RejectedByRt, self::RejectedByRw => 'danger',
            self::Approved => 'info',
            self::Issued, self::Completed => 'success',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::RejectedByRt, self::RejectedByRw, self::Completed], true);
    }

    public function isRejected(): bool
    {
        return in_array($this, [self::RejectedByRt, self::RejectedByRw], true);
    }

    /** Masih bisa diubah oleh warga. */
    public function isEditableByResident(): bool
    {
        return in_array($this, [self::Draft, self::RevisionRequired], true);
    }

    public function hasLetter(): bool
    {
        return in_array($this, [self::Issued, self::Completed], true);
    }

    /** Status yang dihitung "sedang diproses". */
    public static function inProgress(): array
    {
        return [self::PendingRt, self::PendingRw, self::Approved];
    }

    public static function done(): array
    {
        return [self::Issued, self::Completed];
    }
}
