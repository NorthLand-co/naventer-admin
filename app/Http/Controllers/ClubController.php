<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreClubRequest;
use App\Http\Resources\User\ClubResource;
use App\Models\Club;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ClubController extends Controller
{
    public function store(StoreClubRequest $request): JsonResponse
    {
        $club = Club::create(array_merge($request->all(), ['user_id' => Auth::id()]));

        return (new ClubResource($club))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
