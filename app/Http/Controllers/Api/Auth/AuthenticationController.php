<?php

namespace App\Http\Controllers\Api\Auth;

use App\Events\UserLoggedIn;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Notifications\OtpNotification;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Handle the registration of a new user.
     *
     * @param  \App\Http\Requests\RegisterRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $profile = $this->userService->updateUserProfile($user, [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        return response()->json(['user' => $user], Response::HTTP_CREATED);
    }

    /**
     * Handle the authentication of a user.
     *
     * @param  \App\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $request->authenticate();
        $user = Auth::user();
        UserLoggedIn::dispatch($user);
        $request->session()->regenerate();

        return response()->json(['message' => 'Login successfully'], Response::HTTP_OK);
    }

    /**
     * Handle the User changing password requests.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {

        $request->changePassword();

        return response()->json(['message' => 'Password changed successfully'], Response::HTTP_OK);
    }

    /**
     * Handle the logout of the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {

        // Get the currently authenticated user
        $user = Auth::user();

        // For web authentication, you can use the following to log out the user
        Auth::guard('web')->logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate the session token
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out'], Response::HTTP_OK);
    }

    /**
     * Handle the checking authenticate for a username
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function otp(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'otp' => 'nullable|string',
        ]);

        // Apply rate limiting
        $key = 'otp-attempts:'.$request->getClientRealIp();
        $maxAttempts = 5;
        $decaySeconds = 5 * 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        // Increment rate limiter count
        RateLimiter::hit($key, $decaySeconds);

        $user = User::where('email', $request->username)
            ->orWhere('phone', ltrim($request->username, '0'))
            ->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($request->filled('otp')) {
            if ($user->getOtp() === $request->otp) {
                Auth::login($user, true);
                UserLoggedIn::dispatch($user);
                $request->session()->regenerate();
                $user->clearOtp();
                if (is_null($user->phone_verified_at)) {
                    $user->phone_verified_at = Carbon::now();
                    $user->save();
                }

                // Clear rate limit on successful login
                RateLimiter::clear($key);

                return response()->json(['login' => 'success'], Response::HTTP_OK);
            } else {
                throw ValidationException::withMessages([
                    'auth' => __('auth.otp_failed'),
                ]);
            }
        } else {
            $otp = $user->generateOtp();
            $user->notify(new OtpNotification($otp));

            return response()->json(['message' => 'Success OTP sent'], Response::HTTP_OK);
        }
    }

    /**
     * Handle the checking authenticate for a username
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        $user = User::where('email', $request->username)
            ->orWhere('phone', ltrim($request->username, '0'))
            ->first();

        return response()->json([
            'username' => $request->username,
            'has_account' => ! is_null($user),
            'has_password' => ! is_null($user) && ! is_null($user->password),
            'login_method' => ! is_null($user) && is_null($user->phone_verified_at) ? 'otp' : 'password',
        ], Response::HTTP_OK);
    }

    /**
     * Get the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {

        if (Auth::check()) {
            return (new UserResource($request->user()->load(['profile', 'wallet'])))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        }

        return response()->noContent()->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
