<?php

namespace App\Providers;

use App\Models\Disbursement;
use App\Models\Proposal;
use App\Models\Transaction;
use App\Policies\DisbursementPolicy;
use App\Policies\ProposalPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Disbursement::class => DisbursementPolicy::class,
        Transaction::class  => TransactionPolicy::class,
        Proposal::class     => ProposalPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
