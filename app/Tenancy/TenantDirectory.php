<?php

namespace App\Tenancy;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;

class TenantDirectory
{
    public function __construct(private ?ConnectionInterface $connection = null)
    {
    }

    /**
     * @return array<int, array{slug: string, name: string, domains: string[]}>
     */
    public function all(): array
    {
        if ($this->connection === null) {
            return [];
        }

        try {
            $tenants = $this->connection
                ->table('tenants')
                ->orderBy('id')
                ->get();
        } catch (QueryException $exception) {
            return [];
        }

        return $tenants
            ->map(function ($tenant) {
                $data = $this->decodeData($tenant->data ?? null);
                $slug = $data['slug'] ?? (string) ($tenant->id ?? '');
                $name = $data['name'] ?? ($slug !== '' ? $slug : 'Unknown Tenant');

                try {
                    $domains = $this->connection
                        ->table('domains')
                        ->where('tenant_id', $tenant->id)
                        ->orderBy('domain')
                        ->pluck('domain')
                        ->all();
                } catch (QueryException $exception) {
                    $domains = [];
                }

                return [
                    'slug' => $slug,
                    'name' => $name,
                    'domains' => array_values($domains),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeData(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }
}
