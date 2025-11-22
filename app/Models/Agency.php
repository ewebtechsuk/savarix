<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'domain',
        'status',
    ];

    public function setDomainAttribute(?string $domain): void
    {
        $this->attributes['domain'] = self::normalizeDomain($domain);
    }

    public static function normalizeDomain(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (! str_starts_with(strtolower($value), 'http://') && ! str_starts_with(strtolower($value), 'https://')) {
            $value = 'https://' . $value;
        }

        $host = parse_url($value, PHP_URL_HOST) ?: null;

        if (! $host) {
            return null;
        }

        return strtolower(rtrim($host, '/'));
    }

    public function tenantDashboardUrl(): ?string
    {
        if (! $this->domain) {
            return null;
        }

        $host = static::normalizeDomain($this->domain);

        if (! $host) {
            return null;
        }

        return 'https://' . $host . '/dashboard';
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
