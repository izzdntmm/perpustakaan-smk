<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();

            $table->string('borrower_name');
            $table->string('borrower_class', 50);

            // pending | borrowed | returned | cancelled
            $table->string('status', 15)->default('pending');
            // online | walk_in
            $table->string('source', 10)->default('online');

            $table->date('loan_date');
            $table->unsignedSmallInteger('duration_days')->default(7);
            $table->date('due_date')->nullable();

            $table->dateTime('borrowed_at')->nullable();
            $table->dateTime('returned_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('due_date');
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};