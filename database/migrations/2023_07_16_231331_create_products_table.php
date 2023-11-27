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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unique();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->string('status')->nullable();
            $table->string('image')->nullable();
            $table->json('variations')->nullable();
            $table->decimal('price', 16, 2)->default(0);
            $table->string('currency')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
