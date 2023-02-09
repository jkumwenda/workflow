<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //Permissions
        if (Schema::hasTable('permissions')) {
            $permissions = DB::table('permissions')->get()->pluck('name');
            foreach ($permissions as $permission) {
                Gate::define($permission, function ($user) use ($permission){
                    //Administrator only
                    $hasPermission = $user->roles()->whereHas('permissions', function ($query) use ($permission) {
                        $query->where('name', $permission);
                    })->count();
                    return $hasPermission;
                });
            }
        }
    }
}
