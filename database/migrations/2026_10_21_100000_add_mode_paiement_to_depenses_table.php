<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('depenses', 'mode_paiement')) {
            return;
        }

        Schema::table('depenses', function (Blueprint $table) {
            $table->enum('mode_paiement', ['especes', 'orange_money', 'mtn_money', 'wave', 'autre'])
                ->default('especes')->after('montant');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('depenses', 'mode_paiement')) {
            Schema::table('depenses', fn (Blueprint $table) => $table->dropColumn('mode_paiement'));
        }
    }
};
