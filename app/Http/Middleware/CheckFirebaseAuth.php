<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFirebaseAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Se não tiver os dados da sessão do Firebase, manda pro login
        if (!session('projectId') || !session('idToken')) {
            return redirect('/login');
        }

        // Continua a requisição normalmente
        $response = $next($request);

        // Evita que o navegador use a versão em cache ao apertar "voltar"
        return $response->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}
