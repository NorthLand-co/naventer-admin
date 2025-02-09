<?php

namespace App\Observers;

use App\Models\User;
use App\Services\UserService;

class UserObserver
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function created(User $user)
    {
        $timestamp = now()->timestamp;
        $avatarName = "{$user->id}_{$timestamp}.png";
        $avatarPath = '/images/avatars/';
        $avatarFullPath = $avatarPath.$avatarName;
        $setAvatar = false;

        if ($user->email) {
            $setAvatar = $this->userService->setGravatarAvatar($user, $avatarFullPath);
        } elseif ($user->name) {
            $setAvatar = $this->userService->setGeneratedAvatar($user, $avatarFullPath, $avatarPath, $timestamp);
        }

        $this->userService->createUserProfile($user, ['avatar' => $setAvatar ? $avatarFullPath : null]);
        $this->userService->createUserWallet($user);
    }
}
