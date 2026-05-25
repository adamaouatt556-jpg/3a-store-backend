<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->enum('type', ['entree', 'sortie', 'ajustement', 'retour', 'perte'])
                  ->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->enum('type', ['entree', 'sortie', 'ajustement', 'retour'])
                  ->after('user_id');
        });
    }
};