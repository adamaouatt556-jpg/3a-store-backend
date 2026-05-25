<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forfaits', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('type', ['mensuel', 'trimestriel', 'semestriel', 'annuel']);
            $table->decimal('prix', 10, 2);
            $table->integer('nb_boutiques')->default(1)->comment('-1 = illimité');
            $table->integer('nb_vendeurs_par_boutique')->default(3);
            $table->integer('duree_jours');
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forfaits');
    }
};