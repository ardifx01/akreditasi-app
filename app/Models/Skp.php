<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skp extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'tb_skp';
    protected $primaryKey = 'id';

    protected $fillable = ['id_staf', 'judul_skp', 'file_dokumen', 'tahun'];

    public function staff(){
        return $this->belongsTo(Staff::class, 'id_staf');
    }
}
