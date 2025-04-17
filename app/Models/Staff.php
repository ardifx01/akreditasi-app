<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'tb_staff';
    protected $primaryKey = 'id';

    protected $fillable = ['id_staf', 'nama_staff'];

    public function skp(){
        return $this->hasMany(Skp::class, 'id_staf');
    }
    public function pelatihan(){
        return $this->hasMany(Pelatihan::class, 'id_staf');
    }
    public function sertifikasi(){
        return $this->hasMany(Sertifikasi::class, 'id_staf');
    }
}
