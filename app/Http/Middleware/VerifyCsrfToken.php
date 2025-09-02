<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        // PayPal IPN
        'paypal/ipn',
        
        // Stripe webhooks
        'stripe/webhook',
        
        // API routes (protected by Sanctum)
        'api/*',
        
        // Legacy endpoints for backward compatibility
        'checkusername.php',
        'paypal.php',
    ];

    protected function shouldPassThrough($request)
    {
        // Additional CSRF bypass logic for specific conditions
        if ($request->is('admin/*') && !$request->user()?->hasRole('admin')) {
            return false;
        }

        return parent::shouldPassThrough($request);
    }
}