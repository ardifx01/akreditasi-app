<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelatihan extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'tb_pelatihan';
    protected $primaryKey = 'id';

    protected $fillable = ['id_staf', 'judul_pelatihan', 'file_dokumen', 'tahun'];

    public function staff(){
        return $this->belongsTo(Staff::class, 'id_staf');
    }
}
