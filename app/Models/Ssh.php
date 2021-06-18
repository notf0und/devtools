<?php
namespace App\Models;

use App\Traits\LagoonTrait;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin Eloquent
 */
class Ssh extends Model
{
    use LagoonTrait;
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'host',
        'hostname',
        'id',
        'identity_file',
        'log_level',
        'password',
        'port',
        'strict_host_key_checking',
        'user',
        'user_known_host_file',
    ];

    /**
     * Relation with Database.
     */
    public function databases(): HasMany
    {
        return $this->hasMany(Database::class);
    }

    public function command(string $command, Environment $environment = null): string
    {
        $sshCommand =  "ssh ";

        if ($this->is_lagoon && $environment && $environment->attributes['openshiftProjectName']) {
            $sshCommand .= "{$environment->attributes['openshiftProjectName']}@";
        }

        return "{$sshCommand}{$this->attributes['host']} \"$command\"";
    }
}
