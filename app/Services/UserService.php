<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravolt\Avatar\Facade as Avatar;

class UserService
{
    protected $avatar;

    public function __construct(Avatar $avatar)
    {
        $this->avatar = $avatar;
    }

    public function setGravatarAvatar(User $user, string $avatarPath): bool
    {
        $avatarUrl = $this->avatar::create($user->email)->toGravatar(['d' => 'identicon']);
        $client = new Client(['verify' => false]);

        try {
            $response = $client->get($avatarUrl);
            $avatarImage = $response->getBody()->getContents();

            Storage::disk('s3')->put($avatarPath, $avatarImage);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to fetch Gravatar image for user {$user->id}: ".$e->getMessage());

            return false;
        }
    }

    public function setGeneratedAvatar(User $user, string $avatarPath, string $avatarName): bool
    {
        try {
            $avatar = $this->avatar::create($user->name)->toBase64();
            $avatar = saveBase64Image($avatar, $avatarPath, $avatarName);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to generate avatar for user {$user->id}: ".$e->getMessage());

            return false;
        }
    }

    public function createUserProfile(User $user, array $profile): void
    {
        $user->profile()->create($profile);
    }

    public function updateUserProfile(User $user, array $profile): void
    {
        $user->profile()->update($profile);
    }

    public function createUserWallet(User $user): void
    {
        $user->wallet()->create([
            'balance' => 0,
            'credit' => 0,
        ]);
    }
}
