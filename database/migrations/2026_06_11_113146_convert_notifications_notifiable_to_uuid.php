<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('notifications') || ! Schema::hasColumn('notifications', 'notifiable_id')) {
            return;
        }

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE notifications ALTER COLUMN notifiable_id TYPE uuid USING notifiable_id::text::uuid'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE notifications MODIFY notifiable_id CHAR(36) NOT NULL'),
            default => null,
        };
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('notifications') || ! Schema::hasColumn('notifications', 'notifiable_id')) {
            return;
        }

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE notifications ALTER COLUMN notifiable_id TYPE bigint USING notifiable_id::text::bigint'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE notifications MODIFY notifiable_id bigint UNSIGNED NOT NULL'),
            default => null,
        };
    }
};
