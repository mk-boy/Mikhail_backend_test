<?php

namespace App\Http\Middleware;

use App\Models\Master;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Заглушка авторизации.
 *
 * Текущий мастер берётся из заголовка X-Master-Id и кладётся
 * в атрибуты запроса. Достать его можно так:
 *
 *     $master = $request->attributes->get('current_master');
 */
class ResolveCurrentMaster
{
    public function handle(Request $request, Closure $next): Response
    {
        $masterId = $request->header('X-Master-Id');

        if (!empty($masterId)) {
            $request->attributes->set('current_master', Master::find($masterId));
        }

        return $next($request);
    }
}
