<?php

namespace App\Http\Controllers;

use App\Mail\NewInquiryNotification;
use App\Models\Inquiry;
use App\Services\TelegramNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InquiryController extends Controller
{
    /**
     * Store a new public inquiry.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        // 1. Honeypot check
        if ($request->filled('hp_website')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Spam detected.',
                    'errors' => ['hp_website' => ['Spam detected.']],
                ], 422);
            }

            return back()->withErrors(['hp_website' => 'Spam detected.'])->withInput();
        }

        // 2. Time-based spam check
        $timeToken = $request->input('_token_time');
        $isTimeSpam = false;

        if (empty($timeToken)) {
            $isTimeSpam = true;
        } else {
            try {
                $submittedAt = (int) decrypt($timeToken);
                $elapsed = time() - $submittedAt;

                // Reject if submitted in under 2 seconds or if token is older than 24 hours
                if ($elapsed < 2 || $elapsed > 86400) {
                    $isTimeSpam = true;
                }
            } catch (Throwable) {
                $isTimeSpam = true;
            }
        }

        if ($isTimeSpam) {
            $errorMsg = 'Form submitted too quickly or session expired. Please try again.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $errorMsg,
                    'errors' => ['_token_time' => [$errorMsg]],
                ], 422);
            }

            return back()->withErrors(['_token_time' => $errorMsg])->withInput();
        }

        // 3. Validation
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:50', 'required_without:email'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:3', 'max:5000'],
        ], [
            'email.required_without' => 'Please provide either an email address or a phone number.',
            'phone.required_without' => 'Please provide either an email address or a phone number.',
        ]);

        // 4. Create inquiry record
        $inquiry = Inquiry::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'source_url' => $request->input('source_url') ?? $request->header('referer') ?? url()->previous(),
            'ip' => $request->ip(),
            'status' => Inquiry::STATUS_NEW,
        ]);

        // 5. Fire Telegram notification (never throws)
        try {
            $notifier = app(TelegramNotifier::class);
            $notifier->sendInquiry($inquiry);
        } catch (Throwable $e) {
            Log::warning('Telegram notification error: '.$e->getMessage());
        }

        // 6. Optional Email notification (never throws)
        $notificationEmail = site('email');
        $mailDriver = config('mail.default');
        if (! empty($notificationEmail) && ! empty($mailDriver) && $mailDriver !== 'null') {
            try {
                Mail::to($notificationEmail)->send(new NewInquiryNotification($inquiry));
            } catch (Throwable $e) {
                Log::warning('Email inquiry notification error: '.$e->getMessage());
            }
        }

        $successMessage = 'Thank you! Your message has been sent successfully. We will get back to you soon.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'inquiry_id' => $inquiry->id,
            ], 201);
        }

        return back()->with('inquiry_success', $successMessage);
    }
}
