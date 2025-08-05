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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country');
            $table->string('website')->nullable();
            $table->json('operating_hours'); // Store operating hours as JSON
            $table->string('logo')->nullable(); // Store logo file path
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('service_charge', 5, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->json('settings')->nullable(); // Store various settings as JSON
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
