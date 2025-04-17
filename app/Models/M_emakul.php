<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class M_emakul extends Model
{
    use HasFactory;
    protected $table = 'local_emakul';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;
    protected $fillable = [
        'kode',
        'nama',
        'status',
        'local_eprodi_id',
        'semester'
    ];

    protected $dates = ['created_at', 'updated_at'];
    public function eprodi()
    {
        return $this->belongsTo(M_eprodi::class, 'local_eprodi_id');
    }

}
