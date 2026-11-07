<?php

namespace Checkpoint;

use Core\Auth;
use Core\Checkpoint;

/**
 * ============================================================================
 * GuestCheckpoint ('guest')
 * ============================================================================
 * Requires visitor to be a guest (NOT signed in).
 * If already authenticated, halts and redirects away from login/register pages.
 */
class GuestCheckpoint extends Checkpoint
{
    public function enter(): mixed
    {
        if (Auth::check()) {
            $homeRoute = function_exists('config') ? (string) config('auth.home_route', 'home') : 'home';
            $homeUrl = function_exists('config') ? (string) config('auth.home_url', '/') : '/';

            if (function_exists('route')) {
                try {
                    redirect(route($homeRoute));
                    return false;
                } catch (\Throwable) {
                    // Fallback to URL
                }
            }

            if (function_exists('redirect')) {
                redirect($homeUrl);
            }
            return false;
        }

        return true;
    }
}
