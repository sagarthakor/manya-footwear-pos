<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(100) NOT NULL DEFAULT 'cashier'");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','manager','cashier') NOT NULL DEFAULT 'cashier'");
    }
};
