<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterAspect extends Model
{
    protected $fillable = ['name', 'bobot', 'is_modifier'];

    public function indicators()
    {
        return $this->hasMany(MasterIndicator::class);
    }
}
