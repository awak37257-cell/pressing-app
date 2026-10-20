<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pressing_id')->constrained('pressings')->onDelete('cascade');
            $table->string('numero_ticket', 50);
            $table->foreignId('client_id')->constrained('clients')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->dateTime('date_depot');
            $table->date('date_retrait_prevue');
            $table->dateTime('date_retrait_reelle')->nullable();
            $table->enum('statut', ['depose', 'en_traitement', 'pret', 'retire', 'annule'])->default('depose');
            $table->boolean('express')->default(false);
            $table->decimal('montant_total', 12, 0)->default(0);
            $table->decimal('remise', 12, 0)->default(0);
            $table->decimal('montant_paye', 12, 0)->default(0);
            $table->decimal('reste_a_payer', 12, 0)->default(0);
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['pressing_id', 'numero_ticket'], 'uq_ticket');
            $table->index(['pressing_id', 'statut'], 'idx_depots_statut');
            $table->index(['pressing_id', 'date_retrait_prevue'], 'idx_depots_retrait');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depots');
    }
};