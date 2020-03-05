<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Container extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'container_id',
        'image',
        'command',
        'created',
        'status',
        'ports',
        'names',
    ];
}
