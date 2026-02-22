<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Pic        = 'pic';
    case Auditor    = 'auditor';
    case Applicant  = 'applicant';
    case Approver   = 'approver';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Superadmin',
            self::Pic        => 'PIC',
            self::Auditor    => 'Auditor',
            self::Applicant  => 'Pemohon',
            self::Approver   => 'Approver',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Superadmin => 'bg-purple-100 text-purple-800',
            self::Pic        => 'bg-blue-100 text-blue-800',
            self::Auditor    => 'bg-amber-100 text-amber-800',
            self::Applicant  => 'bg-teal-100 text-teal-800',
            self::Approver   => 'bg-indigo-100 text-indigo-800',
        };
    }
}
