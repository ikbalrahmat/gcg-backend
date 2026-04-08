<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterFactor extends Model
{
    protected $fillable = ['master_parameter_id', 'name'];

    public function subFactors()
    {
        // Penamaan relasi mengikuti format JSON React
        return $this->hasMany(MasterSubFactor::class);
    }
}
