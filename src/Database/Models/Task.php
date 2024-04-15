<?php

namespace Pishgaman\WorkReport\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Pishgaman\Pishgaman\Database\Models\User\User as Employee;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id ',
        'title',
        'label',
        'start_date',
        'end_date',
        'all_day',
        'location',
        'description',
        'status'
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'deleted_at',
    ];

    protected $casts = [
        'all_day' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

}
