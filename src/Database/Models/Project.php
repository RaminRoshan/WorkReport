<?php

namespace Pishgaman\WorkReport\Database\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'result_type',
        'sort',
    ];
}
