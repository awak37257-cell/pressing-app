<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clotures_caisse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pressing_id')->constrained('pressings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->date('date');
            $table->decimal('fond_ouverture', 12, 0)->default(0);
            $table->decimal('total_encaisse', 12, 0)->default(0);
            $table->decimal('total_depenses', 12, 0)->default(0);
            $table->decimal('solde_theorique', 12, 0)->default(0);
            $table->decimal('solde_reel', 12, 0)->default(0);
            $table->decimal('ecart', 12, 0)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'date'], 'uq_cloture');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clotures_caisse');
    }
};