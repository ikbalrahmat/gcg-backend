<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterParameter extends Model
{
    protected $fillable = ['master_indicator_id', 'name', 'bobot'];

    public function factors()
    {
        return $this->hasMany(MasterFactor::class);
    }
}
