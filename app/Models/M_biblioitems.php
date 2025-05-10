<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_biblioitems extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'biblioitems';
    protected $primaryKey = 'biblioitemnumber';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'biblionumber',
        'volume',
        'number',
        'itemtype',
        'isbn',
        'issn',
        'ean',
        'publicationyear',
        'publishercode',
        'volumedate',
        'volumedesc',
        'collectiontitle',
        'collectionissn',
        'collectionvolume',
        'editionstatement',
        'editionresponsibility',
        'timestamp',
        'illus',
        'pages',
        'notes',
        'size',
        'place',
        'lccn',
        'url',
        'cn_source',
        'cn_class',
        'cn_item',
        'cn_suffix',
        'cn_sort',
        'agerestriction',
        'totalissues',

    ];
}
