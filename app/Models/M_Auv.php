<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class M_Auv extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'authorised_values';

    protected $fillable = [
        'category',
        'authorised_value',
        'lib',
        'imageurl',
    ];

    public function borrowers()
    {
        return $this->hasMany(M_borrowers::class, 'cardnumber', 'authorised_value')
            ->whereRaw("LEFT(cardnumber, 4) = authorised_values.authorised_value");
    }

    public function visitorCorners()
    {
        return $this->hasMany(M_viscorner::class, 'cardnumber', 'authorised_value')
            ->whereRaw("LEFT(cardnumber, 4) = authorised_values.authorised_value");
    }

    public function visitorHistories()
    {
        return $this->hasMany(M_vishistory::class, 'cardnumber', 'authorised_value')
            ->whereRaw("LEFT(cardnumber, 4) = authorised_values.authorised_value");
    }
}
