<?php

namespace App\Providers;

use App\Models\AcademicProgram;
use App\Models\AvailableProduct;
use App\Models\Booking;
use App\Models\Laboratory;
use App\Models\Loan;
use App\Models\Product;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\User;
use App\Policies\AcademicProgramPolicy;
use App\Policies\AvailableProductPolicy;
use App\Policies\BookingPolicy;
use App\Policies\LaboratoryPolicy;
use App\Policies\LoanPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RolePolicy;
use App\Policies\SchedulePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        AcademicProgram::class => AcademicProgramPolicy::class,
        AvailableProduct::class => AvailableProductPolicy::class,
        Booking::class => BookingPolicy::class,
        Laboratory::class => LaboratoryPolicy::class,
        Loan::class => LoanPolicy::class,
        Product::class => ProductPolicy::class,
        Role::class => RolePolicy::class,
        Schedule::class => SchedulePolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        $configuredSuperAdminRole = (string) config('filament-shield.super_admin.name', 'SUPER-ADMIN');
        $superAdminRoleNames = array_unique([$configuredSuperAdminRole, 'SUPER-ADMIN', 'super_admin']);

        Gate::before(function (User $user) use ($superAdminRoleNames) {
            if ($user->hasRole($superAdminRoleNames)) {
                return true;
            }

            return null;
        });
    }
}
