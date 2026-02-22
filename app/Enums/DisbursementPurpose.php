<?php
namespace App\Enums;
enum DisbursementPurpose: string {
    case Activity    = 'activity';
    case Operational = 'operational';
    public function label(): string { return match($this) { self::Activity => 'Kegiatan', self::Operational => 'Operasional' }; }
}
