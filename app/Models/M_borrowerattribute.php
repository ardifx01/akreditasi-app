<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_borrowerattribute extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'borrower_attributes';
    protected $primaryKey = 'id';



    protected $fillable = [
        'borrowernumber',
        'code',
        'attribute',

    ];


    public function borrower()
    {
        return $this->belongsTo(M_borrowers::class, 'borrowernumber', 'borrowernumber');
    }


    public function authorisedValue()
    {

        return $this->hasOne(M_Auv::class, 'authorised_value', 'attribute')
            ->whereColumn('authorised_values.category', 'borrower_attributes.code');
    }
}
