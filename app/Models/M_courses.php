<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_courses extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'courses';
    protected $primaryKey = 'coursenumber';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'coursename',
        'coursecode',
        'department',
        'section',
        'term',
        'instructors',
        'staffnote',
        'publicnote',
        'link',
        'studentcount',
        'coursestatus'
    ];

}
