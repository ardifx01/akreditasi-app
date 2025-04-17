<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_eclassprod extends Model
{
    use HasFactory;
    protected $table = 'local_eclass_prodi';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'kode',
        'nama',
        'status',
        'local_eprodi_id',
        'semester',
        'wildcard'
    ];
    protected $dates = ['created_at', 'updated_at'];
    public function localEprodi()
    {
        return $this->belongsTo(M_eclassprod::class, 'local_eprodi_id');
    }

}
