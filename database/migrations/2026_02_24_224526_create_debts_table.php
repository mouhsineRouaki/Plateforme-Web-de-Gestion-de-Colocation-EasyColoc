<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('colocation_id')
                ->constrained('colocations')
                ->cascadeOnDelete();

            // from_user_id = débiteur (celui qui doit)
            $table->foreignId('from_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // to_user_id = créancier (celui à qui on doit)
            $table->foreignId('to_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // montant restant à payer (toujours >= 0 en logique)
            $table->decimal('amount', 12, 2)->default(0);

            $table->timestamps();

            // 1 seule ligne par couple (from,to) dans une colocation
            $table->unique(['colocation_id', 'from_user_id', 'to_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};