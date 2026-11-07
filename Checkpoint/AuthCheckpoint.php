<?php

namespace Checkpoint;

use Core\Auth;
use Core\Checkpoint;

/**
 * ============================================================================
 * AuthCheckpoint ('auth')
 * ============================================================================
 * Requires incoming visitor to be signed in.
 * Halts and redirects unauthenticated guests to the login page.
 */
class AuthCheckpoint extends Checkpoint
{
    public function enter(): mixed
    {
        if (Auth::guest()) {
            if (function_exists('flash')) {
                flash('error', 'Please sign in to access this page.');
            }

            $loginRoute = function_exists('config') ? (string) config('auth.login_route', 'auth.login') : 'auth.login';
            $loginUrl = function_exists('config') ? (string) config('auth.login_url', '/login') : '/login';

            if (function_exists('route')) {
                try {
                    redirect(route($loginRoute));
                    return false;
                } catch (\Throwable) {
                    // Fallback to URL if route name not registered
                }
            }

            if (function_exists('redirect')) {
                redirect($loginUrl);
            }
            return false;
        }

        return true;
    }
}
