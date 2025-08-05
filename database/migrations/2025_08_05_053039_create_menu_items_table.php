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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable(); // For profit calculations
            $table->string('image')->nullable();
            $table->json('ingredients')->nullable(); // Store ingredients as JSON array
            $table->json('allergens')->nullable(); // Store allergen information
            $table->json('nutritional_info')->nullable(); // Calories, etc.
            $table->integer('preparation_time')->nullable(); // Minutes
            $table->enum('spice_level', ['none', 'mild', 'medium', 'hot', 'very_hot'])->nullable();
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_vegan')->default(false);
            $table->boolean('is_gluten_free')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_available')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['restaurant_id', 'category_id', 'is_available']);
            $table->index(['is_featured', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
