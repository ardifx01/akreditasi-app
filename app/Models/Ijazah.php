<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ijazah extends Model
{
    protected $connection = 'mysql';
    protected $table = 'tb_ijazah';
    protected $primaryKey = 'id';

    protected $fillable = ['id_staf', 'judul_ijazah', 'file_dokumen', 'tahun'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'id_staf');
    }
}
