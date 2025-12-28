<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if the id column exists
        if (!Schema::hasColumn('attendance_payments_time_schedule', 'id')) {
            try {
                // Try to drop existing primary key if it exists (in case it's on a non-existent column)
                DB::statement('ALTER TABLE `attendance_payments_time_schedule` DROP PRIMARY KEY');
            } catch (\Exception $e) {
                // Primary key might not exist or already dropped, continue
            }
            
            // Add id column using raw SQL to ensure it's first and auto-increment
            DB::statement('ALTER TABLE `attendance_payments_time_schedule` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`)');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This migration adds a required column, so we won't reverse it
        // Removing the id column would break the table structure
    }
};
