<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Modules\CRM\Policies\ClientPolicy;
use App\Modules\CRM\Policies\OpportunityPolicy;
use App\Modules\Identity\Policies\UserPolicy;
use App\Modules\Products\Policies\ProductPolicy;
use App\Modules\Projects\Policies\ProjectPolicy;
use App\Modules\Work\Policies\TaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Opportunity::class, OpportunityPolicy::class);
    }
}
