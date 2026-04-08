<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterIndicator extends Model
{
    protected $fillable = ['master_aspect_id', 'name', 'bobot'];

    public function parameters()
    {
        return $this->hasMany(MasterParameter::class);
    }
}
