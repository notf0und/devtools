<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait LagoonTrait
{
    protected array $hostnames;

    public function __construct()
    {
        $this->hostnames = config('devtools.lagoon.ssh.hostnames');
    }

    public function getIsLagoonAttribute(): bool
    {
        return in_array($this->attributes['hostname'], $this->hostnames);
    }

    public function scopeWithLagoon(Builder $query): Builder
    {
        $select = sprintf(
            '*, IF(sshes.hostname IN ("%s"), TRUE, FALSE) as is_lagoon',
            implode(',', $this->hostnames)
        );
        return $query->addSelect(DB::raw($select));
    }

    public function scopeIsLagoon(Builder $query): Builder
    {
        return $query->whereIn('hostname', $this->hostnames);
    }

    public function scopeIsNotLagoon(Builder $query): Builder
    {
        return $query->whereNotIn('hostname', $this->hostnames);
    }
}
