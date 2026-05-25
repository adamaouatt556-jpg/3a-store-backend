<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boutique_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boutique_id')
                  ->constrained('boutiques')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->enum('role', ['gerant', 'vendeur'])->default('vendeur');
            $table->boolean('actif')->default(true);
            $table->timestamps();

            // Un vendeur ne peut être assigné qu'une fois par boutique
            $table->unique(['boutique_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boutique_user');
    }
};