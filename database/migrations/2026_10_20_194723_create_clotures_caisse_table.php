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
            $table->foreignId('pressing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Qui a fait la clôture
            $table->date('date_cloture');
            
            // Montants théoriques calculés par le système
            $table->decimal('total_especes', 12, 2)->default(0);
            $table->decimal('total_wave', 12, 2)->default(0);
            $table->decimal('total_orange', 12, 2)->default(0);
            $table->decimal('total_mtn', 12, 2)->default(0);
            $table->decimal('total_carte', 12, 2)->default(0);
            $table->decimal('total_general', 12, 2)->default(0);
            
            // Vérification physique de la caisse (Espèces)
            $table->decimal('montant_physique_declare', 12, 2); // Ce que la caissière a compté
            $table->decimal('ecart', 12, 2)->default(0); // Positif = excédent, Négatif = manquant
            
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clotures_caisse');
    }
};