<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Database extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'host',
    ];

    /**
     * Relation with Ssh.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ssh()
    {
        return $this->belongsTo(Ssh::class);
    }
}
