<?php
// app/Models/CustomerTTH.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerTTH extends Model
{
    use TraitModel;
    use HasFactory;

    protected $table = '`dbo.CustomerTTH`';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'TTHNo', 'SalesID', 'TTOTTPNo', 'CustID', 'DocDate',
        'Received', 'ReceivedDate', 'FailedReason'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'CustID', 'CustID');
    }

    public static function getTableWithSchema($alias = 't'){
        return self::getSchemaTable('dbo.CustomerTTH', $alias);
    }
}