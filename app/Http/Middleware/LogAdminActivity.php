<?php

namespace App\Http\Middleware;

use App\Models\AdminLog;
use Closure;
use Illuminate\Http\Request;

/** Records every successful change (POST/PUT/DELETE) made in the admin panel. */
class LogAdminActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Skip refused actions: validation errors or an "error" flash set by the controller.
        $refused = session()->has('errors') || session()->has('error');

        if (! $request->isMethodSafe() && $request->user()?->is_admin && $response->getStatusCode() < 400 && ! $refused
            && $route = $request->route()?->getName()) {
            $subject = collect($request->route()->parameters())
                ->map(fn ($p) => is_object($p) ? ($p->order_number ?? $p->return_number ?? $p->name ?? $p->title ?? $p->code ?? ('#' . $p->getKey())) : $p)
                ->implode(', ');
            // New records have no route parameter yet; describe them by their submitted name.
            $subject = $subject ?: $request->input('name') ?? $request->input('title') ?? $request->input('code');
            AdminLog::record(str_replace('admin.', '', $route), $subject ?: null);
        }

        return $response;
    }
}
