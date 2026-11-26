<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = config('app.admin_key');

        // Si no hay clave configurada, bloquea siempre
        if (empty($configured)) {
            abort(403, 'Panel no disponible: configura ADMIN_KEY en .env');
        }

        $provided = $request->query('key') ?? $request->header('X-Admin-Key');

        if (!hash_equals($configured, (string) $provided)) {
            abort(403, 'Acceso denegado');
        }

        return $next($request);
    }
}
