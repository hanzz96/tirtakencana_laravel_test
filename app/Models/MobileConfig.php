<?php
// app/Models/MobileConfig.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileConfig extends Model
{
    use TraitModel;
    use HasFactory;

    protected $table = 'dbo.MobileConfig';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'BranchCode', 'Name', 'Description', 'Value'
    ];

    public static function getTableWithSchema($alias = 'm'){
        return self::getSchemaTable('dbo.MobileConfig', $alias);
    }
}