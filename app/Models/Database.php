<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Eloquent
 */
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
        'database',
        'port',
        'ssh_id'
    ];

    /**
     * Relation with Ssh.
     */
    public function ssh(): BelongsTo
    {
        return $this->belongsTo(Ssh::class);
    }

    /**
     * Relation with Environment.
     */
    public function environment(): HasOne
    {
        return $this->hasOne(Environment::class);
    }

    /**
     * Scope a query to only include databases on lagoon.
     */
    public function scopeIsLagoon(Builder $query): Builder
    {
        return $query->whereHas('ssh', fn($ssh) => $ssh->isLagoon());
    }

    /**
     * Scope a query to only include databases that are not on lagoon.
     */
    public function scopeIsNotLagoon(Builder $query): Builder
    {
        return $query->whereDoesntHave('ssh', fn($ssh) => $ssh->isLagoon());
    }

    public function getIsLagoonAttribute()
    {
        $ssh = $this->ssh;
        return !!$ssh && $ssh->is_lagoon;
    }

    /**
     * Get mysqldump as as command.
     */
    public function getMysqldumpAttribute(): string
    {
        return $this->mysqldumpCommand();
    }

    /**
     * Get mysql as a command
     */
    public function getMysqlattribute()
    {
        return $this->mysqlCommand();
    }

    /**
     * Generate and return a download path for a database.
     */
    public function getDownloadPathAttribute(): string
    {
        $basePath = 'database/backups';
        $path = $this->attributes['name'];
        $path .= $this->attributes['database'] ? '/' . $this->attributes['database'] : null;

        $environment = $this->environment;
        if ($environment) {
            $path = "{$environment->project->name}/{$environment->name}";
        }

        $fullPath = "{$basePath}/{$path}";
        Storage::makeDirectory($fullPath);

        return Storage::path($fullPath);
    }

    public function mysqlCommand($prepend = null, $append = null)
    {
        $arguments = [
            'mysql',
            '-h',
            $this->attributes['host'],
            '-P',
            $this->attributes['port'],
            '-u',
            $this->attributes['username'],
            '-p' . $this->attributes['password'],
            $this->attributes['database'] ? "'{$this->attributes['database']}'" : null,
        ];

        $command = implode(' ', $arguments);
        $command = implode(' ', [$prepend, $command, $append]);
        $command = ltrim($command, ' ');
        $command = rtrim($command, ' ');

        if ($this->ssh()->exists()) {
            $command = $this->ssh->command($command, $this->environment);
        }
        return $command;
    }

    public function mysqldumpCommand(string $prepend = null, string $append = null): string
    {
        $arguments = implode(' ', [
            'mysqldump',
            '-h',
            $this->attributes['host'],
            '-P',
            $this->attributes['port'],
            '-u',
            $this->attributes['username'],
            '-p' . $this->attributes['password'],
            "'{$this->attributes['database']}'",
            '--opt',
            '--single-transaction',
            '--quick',
            '--compress'
        ]);

        $command = implode(' ', [$prepend, $arguments, $append]);
        $command = ltrim($command, ' ');
        $command = rtrim($command, ' ');

        if ($this->ssh) {
            $command = $this->ssh->command($command, $this->environment);
        }

        return $command;
    }
}
