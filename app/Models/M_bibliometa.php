<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_bibliometa extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'biblio_metadata';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'biblionumber',
        'format',
        'marcflavour',
        'metadata',
        'timestamp',
    ];
}
