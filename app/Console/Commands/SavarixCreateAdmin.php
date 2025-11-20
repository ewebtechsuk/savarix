<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Throwable;

class SavarixCreateAdmin extends Command
{
    protected $signature = 'savarix:create-admin
        {--email=admin@savarix.com : Email for the landlord admin user}
        {--password=SavarixAdmin123! : Password for the landlord admin user}
        {--name="Savarix Admin" : Display name for the landlord admin user}
        {--role=owner : Role to assign to the admin user}
        {--force : Overwrite the password even if the user already exists}';

    protected $description = 'Create or update the Savarix landlord admin user on the central database connection.';

    public function handle(): int
    {
        $centralConnection = config('tenancy.database.central_connection', config('database.default'));
        $email = (string) $this->option('email');
        $password = (string) $this->option('password');
        $name = (string) $this->option('name');
        $role = (string) $this->option('role');
        $force = (bool) $this->option('force');

        try {
            $userQuery = User::on($centralConnection);
            $existing = $userQuery->where('email', $email)->first();

            if ($existing && ! $force) {
                $this->line("Updating existing admin user {$email} without touching password (use --force to reset).");
            }

            $attributes = [
                'name' => $name,
                'role' => $role,
                'is_admin' => true,
            ];

            if (! $existing || $force) {
                $attributes['password'] = Hash::make($password);
            }

            $user = $userQuery->updateOrCreate(
                ['email' => $email],
                $attributes
            );

            $this->info("Admin user ensured on connection [{$centralConnection}].");
            $this->table(['id', 'name', 'email', 'role', 'is_admin', 'connection'], [[
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_admin' => $user->is_admin ? 'yes' : 'no',
                'connection' => $user->getConnectionName() ?? $centralConnection,
            ]]);

            if ($existing && ! $force) {
                $this->comment('Password was left unchanged. Re-run with --force to reset it.');
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Failed to create/update admin user: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
