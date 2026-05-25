<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('forfait_id')
                  ->constrained('forfaits')
                  ->onDelete('cascade');
            $table->enum('statut', ['en_attente', 'actif', 'expire', 'suspendu'])
                  ->default('en_attente');
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->foreignId('valide_par')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('valide_le')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};