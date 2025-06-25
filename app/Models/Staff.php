<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $connection = 'mysql';
    protected $table = 'tb_staff';
    protected $primaryKey = 'id';

    protected $fillable = ['id_staf', 'nama_staff', 'posisi'];

    // Relasi ke setiap tabel one to many
    public function skp()
    {
        return $this->hasMany(Skp::class, 'id_staf');
    }
    public function pelatihan()
    {
        return $this->hasMany(Pelatihan::class, 'id_staf');
    }
    public function sertifikasi()
    {
        return $this->hasMany(Sertifikasi::class, 'id_staf');
    }
    public function transkrip()
    {
        return $this->hasMany(Transkrip::class, 'id_staf');
    }
    public function ijazah()
    {
        return $this->hasMany(Ijazah::class, 'id_staf');
    }
    public function mou()
    {
        return $this->hasMany(Mou::class, 'id_staf');
    }
}
