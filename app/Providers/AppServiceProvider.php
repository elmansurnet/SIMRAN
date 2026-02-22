<?php

namespace App\Providers;

use App\Models\Disbursement;
use App\Models\Transaction;
use App\Policies\DisbursementPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Disbursement::class, DisbursementPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
    }
}
