<?php
namespace App\Enums;
enum BudgetType: string {
    case Monthly = 'monthly';
    case Yearly  = 'yearly';
    public function label(): string { return match($this) { self::Monthly => 'Bulanan', self::Yearly => 'Tahunan' }; }
}
