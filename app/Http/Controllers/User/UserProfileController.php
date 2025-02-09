<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserProfileRequest;
use App\Http\Resources\User\UserResource;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort(Response::HTTP_FORBIDDEN);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserProfile $userProfile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserProfileRequest $request)
    {
        // Retrieve the currently authenticated user
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Update the user's profile with validated request data
        $profile = $user->profile->update($request->all());

        if ($request->has('first_name') || $request->has('last_name')) {
            $user->update([
                'name' => $request->first_name.' '.$request->last_name,
            ]);
        }

        // Return a resource response with HTTP 202 Accepted status
        return (new UserResource($user))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserProfile $userProfile)
    {
        abort(Response::HTTP_FORBIDDEN);
    }
}
