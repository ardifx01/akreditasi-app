<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class M_epustaka extends Model
{
    use HasFactory;
    protected $table = 'local_epustaka';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'biblionumber',
        'local_emakul_id',
        'local_eprodi_id',
        'status',
        'semester'
    ];

    protected $dates = ['created_at', 'updated_at'];
    public function emakuls()
    {
        return $this->belongsTo(M_emakul::class, 'local_emakul_id');
    }

    public function eprodi()
    {
        return $this->belongsTo(M_eprodi::class, 'local_eprodi_id');
    }

}
