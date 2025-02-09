<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactFormSubmitted;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Handle the contact form submission.
     */
    public function submit(StoreContactRequest $request): JsonResponse
    {
        try {
            // Store the contact message in the database
            $contact = Contact::create($request->validated());
            // Send email notification
            Mail::to(config('mail.admin_email'))->send(new ContactFormSubmitted($contact));

            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent and stored successfully.',
                'data' => $contact,
            ], 200);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Contact form submission failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'There was an error submitting your message. Please try again later.',
            ], 500);
        }
    }
}
