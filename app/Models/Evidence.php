<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{
    // 🔧 FIX: Memaksa Laravel pakai tabel 'evidences', bukan 'evidence'
    protected $table = 'evidences';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
