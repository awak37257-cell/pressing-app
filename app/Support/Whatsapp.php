<?php

namespace App\Support;

class Whatsapp
{
    /**
     * Lien wa.me avec message pré-rempli (aucun service d'envoi payant nécessaire).
     * Numéros ivoiriens : 10 chiffres (01, 05, 07…) → l'indicatif 225 est ajouté s'il manque.
     */
    public static function url(?string $telephone, string $message): ?string
    {
        $chiffres = preg_replace('/\D+/', '', (string) $telephone);

        if ($chiffres === '') {
            return null;
        }

        if (str_starts_with($chiffres, '00')) {
            $chiffres = substr($chiffres, 2);
        }

        if (! str_starts_with($chiffres, '225')) {
            $chiffres = '225' . $chiffres;
        }

        return 'https://wa.me/' . $chiffres . '?text=' . rawurlencode($message);
    }
}
