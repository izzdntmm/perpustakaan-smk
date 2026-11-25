<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->unsignedSmallInteger('year_published')->nullable();
            $table->string('isbn', 20)->nullable();
            $table->text('synopsis')->nullable();
            $table->string('cover_path')->nullable();

            // digital | physical
            $table->string('type', 10)->default('physical');

            // Khusus buku digital
            $table->string('pdf_path')->nullable();

            // Khusus buku fisik
            $table->unsignedInteger('stock_total')->default(0);
            $table->unsignedInteger('stock_available')->default(0);
            $table->string('shelf_location', 50)->nullable();

            $table->timestamps();

            $table->index('title');
            $table->index('author');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};