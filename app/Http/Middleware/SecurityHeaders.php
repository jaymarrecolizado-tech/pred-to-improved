<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        /*
         * Strict-Transport-Security (HSTS)
         * Forces browsers to use HTTPS for the next 1 year.
         * includeSubDomains covers all subdomains.
         */
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains'
        );

        /*
         * Content-Security-Policy (CSP)
         * Restricts which sources can load scripts, styles, and other assets.
         * Allows Filament assets, ui-avatars.com for profile photos,
         * and inline styles/scripts required by Livewire and Filament.
         */
        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data: blob: https://ui-avatars.com",
                "font-src 'self' data:",
                "connect-src 'self'",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ])
        );

        /*
         * X-Frame-Options
         * Prevents the site from being embedded in iframes (clickjacking protection).
         */
        $response->headers->set('X-Frame-Options', 'DENY');

        /*
         * X-Content-Type-Options
         * Prevents browsers from MIME-sniffing the content type.
         */
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        /*
         * Referrer-Policy
         * Controls how much referrer information is sent with requests.
         */
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        /*
         * Permissions-Policy
         * Disables browser features not needed by this application.
         */
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=()'
        );

        return $response;
    }
}