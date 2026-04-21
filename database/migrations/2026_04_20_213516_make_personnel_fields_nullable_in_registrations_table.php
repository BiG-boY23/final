<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            $table->string('contact_number', 20)->nullable()->change();
            $table->string('university_id')->nullable()->change();
            $table->string('email_address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            $table->string('contact_number', 20)->nullable(false)->change();
            $table->string('university_id')->nullable(false)->change();
            $table->string('email_address')->nullable(false)->change();
        });
    }
};
