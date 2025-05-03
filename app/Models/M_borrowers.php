<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_borrowers extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'borrowers';
    protected $primaryKey = 'borrowernumber';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'cardnumber',
        'surname',
        'firstname',
        'title',
        'othernames',
        'initials',
        'streetnumber',
        'streettype',
        'address',
        'address2',
        'city',
        'state',
        'zipcode',
        'country',
        'email',
        'phone',
        'mobile',
        'fax',
        'emailpro',
        'phonepro',
        'B_streetnumber',
        'B_streettype',
        'B_address',
        'B_address2',
        'B_city',
        'B_state',
        'B_zipcode',
        'B_country',
        'B_email',
        'B_phone',
        'dateofbirth',
        'branchcode',
        'categorycode',
        'dateenrolled',
        'dateexpiry',
        'date_renewed',
        'gonenoaddress',
        'lost',
        'debarred',
        'debarredcomment',
        'contactname',
        'contactfirstname',
        'contacttitle',
        'guarantorid',
        'borrowernotes',
        'relationship',
        'ethnicity',
        'ethnotes',
        'sex',
        'password',
        'flags',
        'userid',
        'opacnote',
        'contactnote',
        'sort1',
        'sort2',
        'altcontactfirstname',
        'altcontactsurname',
        'altcontactaddress1',
        'altcontactaddress2',
        'altcontactaddress3',
        'altcontactstate',
        'altcontactzipcode',
        'altcontactcountry',
        'altcontactphone',
        'smsalertnumber',
        'sms_provider_id',
        'privacy',
        'privacy_guarantor_checkouts',
        'checkprevcheckout',
        'updated_on',
        'lastseen',
        'lang',
        'login_attempts',
        'overdrive_auth_token'
    ];

    public function visitHistory(){
        return $this->hasMany(M_borrowers::class, 'cardnumber', 'cardnumber');
    }

    public function programStudi(){
        return $this->belongsTo(M_Auv::class, 'cardnumber', 'authorised_value')
            ->whereRaw("LEFT(cardnumber, 4) = authorised_value");
    }

    // // Relasi ke tabel kategori
    // public function category(): BelongsTo
    // {
    //     return $this->belongsTo(Category::class, 'categorycode', 'categorycode');
    // }

    // // Relasi ke tabel cabang (branches)
    // public function branch(): BelongsTo
    // {
    //     return $this->belongsTo(Branch::class, 'branchcode', 'branchcode');
    // }

    // // Relasi ke tabel SMS Provider
    // public function smsProvider(): BelongsTo
    // {
    //     return $this->belongsTo(SmsProvider::class, 'sms_provider_id', 'id');
    // }

}
