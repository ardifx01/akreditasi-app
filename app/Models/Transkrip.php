<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transkrip extends Model
{
    protected $table = 'tb_transkrip';
    protected $primaryKey = 'id';

    protected $fillable = ['id_staf', 'judul_transkrip', 'file_dokumen', 'tahun'];

    public function staff(){
        return $this->belongsTo(Staff::class, 'id_staf');
    }
}
