<?php

namespace Checkpoint;

use Core\Auth;
use Core\Checkpoint;

/**
 * ============================================================================
 * AdminCheckpoint ('admin')
 * ============================================================================
 * Requires visitor to be signed in AND possess administrator privileges.
 * Aborts with HTTP 403 Forbidden if unauthorized.
 */
class AdminCheckpoint extends Checkpoint
{
    public function enter(): mixed
    {
        $user = Auth::user();

        if (!$user) {
            if (function_exists('flash')) {
                flash('error', 'Administrator login required.');
            }
            if (function_exists('redirect')) {
                redirect('/login');
            }
            return false;
        }

        $isAdmin = false;
        if (method_exists($user, 'isAdmin')) {
            $isAdmin = (bool) $user->isAdmin();
        } elseif (isset($user->role)) {
            $isAdmin = ($user->role === 'admin');
        }

        if (!$isAdmin) {
            if (function_exists('abort')) {
                abort(403, 'Forbidden: Administrator privileges required.');
            }
            return false;
        }

        return true;
    }
}

