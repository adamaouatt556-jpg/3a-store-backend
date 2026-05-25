<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventaire_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')
                  ->constrained('inventaires')
                  ->onDelete('cascade');
            $table->foreignId('produit_id')
                  ->constrained('produits')
                  ->onDelete('cascade');
            $table->integer('stock_systeme');
            $table->integer('stock_reel')->default(0);
            $table->integer('ecart')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaire_lignes');
    }
};