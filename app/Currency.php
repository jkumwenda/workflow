<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Currency extends Model
{
    protected static function boot()
    {
        parent::boot();

        // Default order by
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('sort', 'asc')->orderBy('code', 'asc');
        });
    }

    static function getCurrencies()
    {
        $currencies = static::get();
        $return = [];
        foreach ($currencies as $currency) {
            $return[$currency->code] = sprintf("%s (%s)", $currency->code, $currency->currency);
        }
        return $return;
    }

}
