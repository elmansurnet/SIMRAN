<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Draft     = 'draft';
    case Forwarded = 'forwarded';
    case Approved  = 'approved';
    case Rejected  = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Menunggu Review',
            self::Forwarded => 'Menunggu Persetujuan',
            self::Approved  => 'Disetujui',
            self::Rejected  => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft     => 'amber',
            self::Forwarded => 'blue',
            self::Approved  => 'green',
            self::Rejected  => 'red',
        };
    }
}
