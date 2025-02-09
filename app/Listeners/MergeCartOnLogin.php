<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Http\Controllers\Ecommerce\Cart\UserCartController;
use Illuminate\Support\Facades\Session;

class MergeCartOnLogin
{
    public function handle(UserLoggedIn $event)
    {
        $userId = $event->user->id;

        // Retrieve the pre-login session ID from the session
        $sessionId = Session::get('temporary_token');

        if ($sessionId) {
            // Logic to merge the session-based cart with the user cart
            app(UserCartController::class)
                ->mergeSessionCartWithUserCart($sessionId, $userId);

            // Clear the temporary session variable
            Session::forget('pre_login_session_id');
        }
    }
}
