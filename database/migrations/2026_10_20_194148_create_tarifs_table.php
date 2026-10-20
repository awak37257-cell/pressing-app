<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_article_id')->constrained('types_articles')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->decimal('prix', 12, 0);
            $table->decimal('prix_express', 12, 0)->default(0);
            $table->timestamps();

            $table->unique(['type_article_id', 'service_id'], 'uq_tarif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifs');
    }
};