<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    public static function getModuleName($moduleId) {
        $modules = config('const.MODULE');
        $moduleName = '';
        foreach ($modules as $moduleDetails) {
            foreach ($moduleDetails as $moduleDetailName => $moduleDetailId) {
                if ($moduleDetailId == $moduleId) $moduleName = $moduleDetailName;
            }
        }

        return $moduleName;
    }
}
