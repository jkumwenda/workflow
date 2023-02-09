<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\Relation;

class RplusServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Requisition', \App\Services\Requisition::class);
        foreach (\File::glob(app_path() . '/Helpers/*.php') as $file) {
            require_once($file);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //Custom Polymorphic Types
        Relation::morphMap([
            'procurements' => \App\Procurement::class,
            'purchases' => \App\Purchase::class,
            'users' => \App\User::class,
            'orders' => \App\Order::class,
            'vouchers' => \App\Voucher::class,
            'travels' => \App\Travel::class,
            'transports' => \App\Transport::class,
            'subsistences' => \App\Subsistence::class,
        ]);

        Validator::extend('check_array', function ($attribute, $value, $parameters, $validator) {
            return count(array_filter($value, function($var) use ($parameters) { return ( $var && $var >= $parameters[0]); }));
       });
    }
}
