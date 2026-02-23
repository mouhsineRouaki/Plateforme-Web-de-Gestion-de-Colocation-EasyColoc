<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('colocation_user', function (Blueprint $table) {
            $table->foreignId('colocation_id')->constrained('colocations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('role_in_colocation')->default('MEMBER'); 
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();

            $table->timestamps();

            $table->primary(['colocation_id', 'user_id']);
            $table->index(['user_id', 'left_at']);
            $table->index(['colocation_id', 'left_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colocation_user');
    }
};