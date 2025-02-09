<?php

namespace App\Services;

use App\Models\Club;
use App\Notifications\ClubNotification;
use Illuminate\Support\Facades\Validator;

class ClubService
{
    public function notify(array $data)
    {
        $validator = Validator::make($data, [
            'message' => 'required|string',
            'receptors' => 'nullable|array',
            'receptors.*' => 'integer|exists:clubs,id',
        ]);

        // If validation fails, return the error messages as JSON response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $message = $data['message'];

        if (isset($data['receptors']) && count($data['receptors']) > 0) {
            $clubs = Club::whereIn('id', $data['receptors'])->get();
        } else {
            $clubs = Club::all();
        }

        foreach ($clubs as $club) {
            $club->notify(new ClubNotification($message));
        }

        return response()->json(['message' => 'Notifications sent!']);
    }
}
