<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;


class Requisition extends Facade
{
  protected static function getFacadeAccessor() {
    return 'Requisition';
  }
}