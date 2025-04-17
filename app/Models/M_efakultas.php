<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class M_efakultas extends Model
{
    use HasFactory;
    protected $table = 'local_efakultas';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'kode',
        'nama',
        'status'
    ];
    protected $dates = ['created_at', 'updated_at'];

}
