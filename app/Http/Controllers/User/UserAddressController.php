<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreAddressRequest;
use App\Http\Requests\User\UpdateAddressRequest;
use App\Http\Resources\User\AddressResource;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $addresses = $user->addresses()->latest()->get();

        return AddressResource::collection($addresses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAddressRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($request->get('is_default', false)) {
            $this->unsetDefaultAddress($user);
        }

        $address = $user->addresses()->create($request->validated());

        return new AddressResource($address);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserAddress $userAddress)
    {

        $this->authorizeAccess($userAddress);

        return new AddressResource($userAddress);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserAddress $userAddress)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAddressRequest $request, $userAddress)
    {
        $address = UserAddress::where('id', $userAddress)->firstOrFail();
        $this->authorizeAccess($address);

        if ($request->get('is_default', false)) {
            $this->unsetDefaultAddress($address->user);
        }

        $address->update($request->validated());

        return new AddressResource($address);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserAddress $address)
    {

        $this->authorizeAccess($address);

        $address->delete();

        return response()->noContent();
    }

    /**
     * Unset the current default address for the user.
     *
     * @param  \App\Models\User  $user
     */
    protected function unsetDefaultAddress($user): void
    {
        $user->addresses()->where('is_default', true)->update(['is_default' => false]);
    }

    /**
     * Authorize the user's access to the address.
     *
     * @param  \App\Models\Address  $address
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorizeAccess(UserAddress $address): void
    {
        if ($address->user_id !== Auth::id()) {
            abort(Response::HTTP_UNAUTHORIZED, 'Unauthorized access to address');
        }
    }
}
