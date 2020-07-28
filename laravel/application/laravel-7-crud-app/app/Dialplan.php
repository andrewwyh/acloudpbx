<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dialplan extends Model
{
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'ext_number',
        'company',
        'technology',
        'dialstring1',
        'context',
        ];
    
    public $timestamps = false;
}
