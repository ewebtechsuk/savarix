<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoices')) {
            return;
        }

        if (! Schema::hasColumn('invoices', 'date')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->date('date')->nullable()->after('number');
            });

            return;
        }

        $this->ensureDateColumnAllowsNullValues();
    }

    public function down(): void
    {
        if (! Schema::hasTable('invoices') || ! Schema::hasColumn('invoices', 'date')) {
            return;
        }

        $this->revertDateColumnNullability();
    }

    private function ensureDateColumnAllowsNullValues(): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $column = collect(DB::select("SHOW COLUMNS FROM `invoices` WHERE Field = 'date'"))->first();

            if ($column && isset($column->Null) && strtolower($column->Null) === 'no') {
                DB::statement('ALTER TABLE `invoices` MODIFY `date` DATE NULL');
            }

            return;
        }

        if ($driver === 'pgsql') {
            $column = collect(DB::select("SELECT is_nullable FROM information_schema.columns WHERE table_name = 'invoices' AND column_name = 'date'")).first();

            if ($column && isset($column->is_nullable) && strtolower($column->is_nullable) === 'no') {
                DB::statement('ALTER TABLE "invoices" ALTER COLUMN "date" DROP NOT NULL');
            }

            return;
        }

        if ($driver === 'sqlite') {
            // SQLite stores all columns as nullable by default unless a NOT NULL constraint is explicitly set, and modifying
            // constraints requires rebuilding the table. We skip adjustments here because fresh installations already
            // include the nullable column in the base migration.
            return;
        }
    }

    private function revertDateColumnNullability(): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `invoices` MODIFY `date` DATE NOT NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE "invoices" ALTER COLUMN "date" SET NOT NULL');

            return;
        }

        if ($driver === 'sqlite') {
            // SQLite does not support altering a column's nullability without recreating the table. We intentionally skip
            // reverting the constraint in development environments that rely on SQLite.
        }
    }
};
