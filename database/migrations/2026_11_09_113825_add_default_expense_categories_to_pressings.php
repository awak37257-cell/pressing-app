<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categoryNames = [
            'Électricité',
            'Eau',
            'Salaires',
            'Loyer',
            'Achats de produits',
            'Remboursement client',
        ];

        foreach (DB::table('pressings')->pluck('id') as $pressingId) {
            foreach ($categoryNames as $name) {
                DB::table('categories_depenses')->insertOrIgnore([
                    'pressing_id' => $pressingId,
                    'nom' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep expense categories and any user data created from them on rollback.
    }
};
