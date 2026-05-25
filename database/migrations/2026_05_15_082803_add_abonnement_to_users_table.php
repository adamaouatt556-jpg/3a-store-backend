<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('valide')->default(false)->after('actif');
            $table->foreignId('forfait_actif_id')
                  ->nullable()
                  ->after('valide')
                  ->constrained('forfaits')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['forfait_actif_id']);
            $table->dropColumn(['valide', 'forfait_actif_id']);
        });
    }
};