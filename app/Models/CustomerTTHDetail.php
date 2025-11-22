<?php
// app/Models/CustomerTTHDetail.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerTTHDetail extends Model
{
    use TraitModel;
    use HasFactory;

    protected $table = 'dbo.CustomerTTHDetail';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'TTHNo', 'TTOTTPNo', 'Jenis', 'Qty', 'Unit'
    ];

    protected $casts = [
        'Qty' => 'integer'
    ];

    // Relationship ke CustomerTTH
    public function customerTth()
    {
        return $this->belongsTo(CustomerTTH::class, 'TTHNo', 'TTHNo');
    }

    public static function getTableWithSchema($alias = 'd'){
        return self::getSchemaTable('dbo.CustomerTTHDetail', $alias);
    }
}