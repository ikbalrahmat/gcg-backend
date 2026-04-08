<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model {
    protected $keyType = 'string'; // Wajib karena primary key kita string
    public $incrementing = false;
    protected $fillable = ['id', 'year', 'tb', 'no_st', 'pt', 'kt', 'status', 'created_by', 'data', 'final_report_url', 'final_report_name'];
    protected $casts = [ 'data' => 'array' ]; // Otomatis ubah JSON jadi Array

    public function members() {
        return $this->hasMany(AssessmentMember::class);
    }
}
