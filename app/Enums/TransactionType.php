<?php
namespace App\Enums;
enum TransactionType: string {
    case Expense = 'expense';
    case Income  = 'income';
    public function label(): string { return match($this) { self::Expense => 'Pengeluaran', self::Income => 'Pemasukan' }; }
}
