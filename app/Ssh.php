<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ssh extends Model
{
    /**
     * Relation with Database.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function databases()
    {
        return $this->hasMany(Database::class);
    }
}
