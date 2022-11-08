<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use App\Models\UserLoan;
use App\Policies\UserLoanPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        UserLoan::class => UserLoanPolicy::class,
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
        Passport::tokensCan([
            'create-loan' => 'Create Loan',
            'view-loan' => 'View Own single Loan',
            'view-all-own-loans' => 'View all Owned Loans',
            'add-repayment' => 'Add Repayment For Loan',
            'approve-loan' => 'Admin can approve loan',
            'view-all-loans' => 'Admin can view all loans',
        ]);
    }
}
