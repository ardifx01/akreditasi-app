<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class M_eprodi extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $table = 'local_eprodi';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'kode',
        'nama',
        'local_efakultas_id',
        'status'
    ];

    protected $dates = ['created_at', 'updated_at'];
    public function efakultas()
    {
        return $this->belongsTo(M_efakultas::class, 'local_efakultas_id');
    }

    public function emakuls()
    {
        return $this->hasMany(M_emakul::class, 'local_eprodi_id');
    }

}
