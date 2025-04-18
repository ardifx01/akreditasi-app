<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mou extends Model
{
    protected $table = 'tb_mou';
    protected $primaryKey = 'id';

    protected $fillable = ['id_staf', 'judul_mou', 'file_dokumen', 'tahun'];

    public function staff(){
        return $this->belongsTo(Staff::class, 'id_staf');
    }
}
