<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentMember extends Model {
    protected $fillable = ['assessment_id', 'name', 'aspectId'];
}
