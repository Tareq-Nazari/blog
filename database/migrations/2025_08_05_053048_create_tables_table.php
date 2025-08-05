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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('number'); // Table number/identifier
            $table->integer('capacity'); // Number of seats
            $table->enum('type', ['indoor', 'outdoor', 'private', 'bar'])->default('indoor');
            $table->text('description')->nullable();
            $table->decimal('x_position', 8, 2)->nullable(); // For floor plan layout
            $table->decimal('y_position', 8, 2)->nullable(); // For floor plan layout
            $table->enum('status', ['available', 'occupied', 'reserved', 'cleaning', 'maintenance'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['restaurant_id', 'number']);
            $table->index(['restaurant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
