<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles_depot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_id')->constrained('depots')->onDelete('restrict');
            $table->foreignId('type_article_id')->constrained('types_articles')->onDelete('restrict');
            $table->foreignId('service_id')->constrained('services')->onDelete('restrict');
            $table->unsignedInteger('quantite')->default(1);
            $table->decimal('prix_unitaire', 12, 0);
            $table->string('couleur', 50)->nullable();
            $table->string('marque', 100)->nullable();
            $table->text('etat_constate')->nullable();
            $table->enum('statut', ['depose', 'en_traitement', 'pret', 'retire', 'perdu', 'abime'])->default('depose');
            $table->text('observation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles_depot');
    }
};