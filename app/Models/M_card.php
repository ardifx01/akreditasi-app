<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class M_card extends Model
{
    use HasFactory;

    protected $table = 'counter_newcard';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'cardnumber',
        'timer',
        'date'
    ];

    protected $dates = ['date'];
}
