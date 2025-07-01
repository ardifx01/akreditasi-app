<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_statistics extends Model
{

    protected $connection = 'mysql2';
    protected $table = 'statistics';

    public $timestamps = false;

    protected $fillable = [
        'datetime',
        'branch',
        'proccode',
        'value',
        'type',
        'other',
        'usercode',
        'itemnumber',
        'itemtype',
        'location',
        'borrowernumber',
        'associatedborrower',
        'ccode',
    ];


    public function borrower()
    {
        return $this->belongsTo(M_borrowers::class, 'borrowernumber', 'borrowernumber');
    }
}
