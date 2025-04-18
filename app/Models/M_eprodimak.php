<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class M_eprodimak extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $table = 'local_eprodi_makul';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;
    protected $fillable = [
        'local_efakultas_id',
        'local_eprodi_id',
        'status',
        'description',
        'local_emakul_id',
        'semester'
    ];

    protected $dates = ['created_at', 'updated_at'];
    public function efakultas()
    {
        return $this->belongsTo(M_efakultas::class, 'local_efakultas_id');
    }

    public function eprodi()
    {
        return $this->belongsTo(M_eprodi::class, 'local_eprodi_id');
    }

    public function emakuls()
    {
        return $this->belongsTo(M_emakul::class, 'local_emakul_id');
    }

}
