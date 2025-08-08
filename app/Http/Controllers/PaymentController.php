<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Tenancy;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function create(Tenancy $tenancy)
    {
        return view('payments.create', compact('tenancy'));
    }

    public function store(Request $request, Tenancy $tenancy)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.5',
        ]);

        $stripe = new StripeClient(config('services.stripe.secret'));

        $intent = $stripe->paymentIntents->create([
            'amount' => (int) ($validated['amount'] * 100),
            'currency' => 'gbp',
            'metadata' => ['tenancy_id' => $tenancy->id],
        ]);

        Payment::create([
            'tenancy_id' => $tenancy->id,
            'amount' => $validated['amount'],
            'status' => 'pending',
            'stripe_reference' => $intent->id,
        ]);

        return response()->json(['client_secret' => $intent->client_secret]);
    }

    public function webhook(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');
        $signature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($request->getContent(), $signature, $secret);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;
            Payment::where('stripe_reference', $intent->id)
                ->update(['status' => 'succeeded']);
        }

        return response()->json(['status' => 'ok']);
    }
}
