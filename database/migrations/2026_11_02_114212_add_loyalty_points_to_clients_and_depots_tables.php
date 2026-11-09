<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->unsignedBigInteger('points_fidelite')->default(0);
            $table->unsignedSmallInteger('reste_fidelite_fcfa')->default(0);
        });

        Schema::table('depots', function (Blueprint $table): void {
            $table->unsignedBigInteger('points_fidelite_utilises')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depots', function (Blueprint $table): void {
            $table->dropColumn('points_fidelite_utilises');
        });

        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn(['points_fidelite', 'reste_fidelite_fcfa']);
        });
    }
};
