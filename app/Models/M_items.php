<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_items extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'items';
    protected $primaryKey = 'itemnumber';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'biblionumber',
        'biblioitemnumber',
        'barcode',
        'dateaccessioned',
        'booksellerid',
        'homebranch',
        'price',
        'replacementprice',
        'replacementpricedate',
        'datelastborrowed',
        'datelastseen',
        'stack',
        'notforloan',
        'damaged',
        'itemlost',
        'itemlost_on',
        'withdrawn',
        'withdrawn_on',
        'itemcallnumber',
        'coded_location_qualifier',
        'issues',
        'renewals',
        'reserves',
        'restricted',
        'itemnotes',
        'itemnotes_nonpublic',
        'holdingbranch',
        'paidfor',
        'location',
        'permanent_location',
        'onloan',
        'cn_source',
        'cn_sort',
        'ccode',
        'materials',
        'uri',
        'itype',
        'more_subfields_xml',
        'enumchron',
        'copynumber',
        'stocknumber',
        'new_status'
    ];

    public function biblio()
    {
        return $this->belongsTo(M_biblio::class, 'biblionumber', 'biblionumber');
    }

}
