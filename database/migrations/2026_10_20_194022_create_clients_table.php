<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pressing_id')->constrained('pressings')->onDelete('cascade');
            $table->string('nom', 100);
            $table->string('prenom', 100)->nullable();
            $table->string('telephone', 30);
            $table->text('adresse')->nullable();
            $table->string('email', 150)->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['pressing_id', 'telephone'], 'uq_client_tel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};