<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('media') || ! Schema::hasColumn('media', 'model_id')) {
            return;
        }

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE media ALTER COLUMN model_id TYPE CHAR(36) USING model_id::text'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE media MODIFY model_id CHAR(36) NOT NULL'),
            default => null,
        };
    }

    public function down(): void
    {
        //
    }
};
