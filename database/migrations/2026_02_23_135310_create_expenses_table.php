<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('colocation_id')->constrained('colocations')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('payer_id')->constrained('users')->restrictOnDelete();

            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->date('spent_at');
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['colocation_id', 'spent_at']);
            $table->index(['category_id']);
            $table->index(['payer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};