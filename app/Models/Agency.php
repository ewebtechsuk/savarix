<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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

    public static function normalizeDomain(?string $domain): ?string
    {
        if ($domain === null) {
            return null;
        }

        $normalized = Str::of($domain)
            ->lower()
            ->trim()
            ->replace(['http://', 'https://'], '')
            ->trim('/')
            ->toString();

        $host = parse_url('https://' . $normalized, PHP_URL_HOST) ?: $normalized;

        return $host ?: null;
    }

    public static function forceHttpsDomain(string $domain): string
    {
        if (str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')) {
            return preg_replace('#^http://#', 'https://', $domain);
        }

        return 'https://' . $domain;
    }

    public function tenantDashboardUrl(): ?string
    {
        if (! $this->domain) {
            return null;
        }

        return rtrim(self::forceHttpsDomain($this->domain), '/') . '/dashboard';
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
