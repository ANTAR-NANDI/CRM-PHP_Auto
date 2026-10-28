<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReplaceLegacyBrand
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $contentType = (string) $response->headers->get('Content-Type');

        if (str_contains($contentType, 'text/html')) {
            $response->setContent(str_replace(
                ['W3xplorers Bangladesh', 'W3 CRM'],
                ['PHP CRM', 'PHP CRM'],
                $response->getContent(),
            ));
        }

        return $response;
    }
}
