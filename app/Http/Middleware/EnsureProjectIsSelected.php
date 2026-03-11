<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectIsSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('active_project_id')) {
            return redirect()->route('projects.index')
                ->with('error', 'Silakan pilih proyek untuk mengakses Dashboard.');
        }

        return $next($request);
    }
}
