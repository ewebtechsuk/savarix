<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function show()
    {
        $stripeKey = env('STRIPE_KEY');
        return view('checkout', compact('stripeKey'));
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'users' => 'required|integer|min:1',
            'payment_method_id' => 'required',
            'cardholder_name' => 'required',
            'billing_email' => 'required|email',
            'billing_address' => 'required',
        ]);

        $amount = ($validated['users']-1 > 0) ? ($validated['users']-1)*10*100 : 0; // in pence
        if ($amount <= 0) {
            return back()->with('success', 'No payment required for first user.');
        }

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'gbp',
                'payment_method' => $validated['payment_method_id'],
                'confirmation_method' => 'manual',
                'confirm' => true,
                'receipt_email' => $validated['billing_email'],
                'description' => 'savirix subscription for '.($validated['users']-1).' additional users',
            ]);
            return back()->with('success', 'Payment successful!');
        } catch (\Exception $e) {
            Log::error('Stripe error: '.$e->getMessage());
            return back()->withErrors(['payment' => 'Payment failed: '.$e->getMessage()]);
        }
    }
}
