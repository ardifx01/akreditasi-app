<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sertifikasi extends Model
{
    protected $table = 'tb_sertifikasi';
    protected $primaryKey = 'id';

    protected $fillable = ['id_staf', 'judul_sertifikasi', 'file_dokumen', 'tahun'];

    public function staff(){
        return $this->belongsTo(Staff::class, 'id_staf');
    }
}
