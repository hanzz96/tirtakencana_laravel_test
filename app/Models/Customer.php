<?php
// app/Models/Customer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use TraitModel;
    use HasFactory;

    protected $table = 'dbo.Customer';
    protected $primaryKey = 'CustID';
    // public $incrementing = false;
    // protected $keyType = 'string';

    protected $fillable = [
        'CustID', 'Name', 'Address', 'BranchCode', 'PhoneNo'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = DB::raw('`dbo.Customer`');
        // dd($this->table);
    }

    public function tth()
    {
        return $this->hasMany(CustomerTTH::class, 'CustID', 'CustID');
    }

    public static function getTableWithSchema($alias = 'c'){
        return self::getSchemaTable('dbo.Customer', $alias);
    }
}