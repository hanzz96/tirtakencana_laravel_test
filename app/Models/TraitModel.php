<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait TraitModel
{
    public static function getSchemaTable($table, $alias)
    {
        return DB::raw("`$table` as $alias");
    }
}