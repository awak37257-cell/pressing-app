<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->doit_changer_mot_de_passe) {
            // Autoriser uniquement l'accès à la page de changement de mot de passe et à la déconnexion
            if (!$request->routeIs('password.force.change', 'password.force.update', 'logout')) {
                return redirect()->route('password.force.change');
            }
        }

        return $next($request);
    }
}