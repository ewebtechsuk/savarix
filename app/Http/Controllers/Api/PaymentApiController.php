<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;

class PaymentApiController extends Controller
{
    public function index()
    {
        $payments = Payment::paginate(20);
        return PaymentResource::collection($payments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenancy_id' => 'required|exists:tenancies,id',
            'amount' => 'required|numeric',
            'status' => 'nullable|string',
            'stripe_reference' => 'nullable|string',
        ]);
        $payment = Payment::create($validated);
        return new PaymentResource($payment);
    }

    public function show(Payment $payment)
    {
        return new PaymentResource($payment);
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'tenancy_id' => 'sometimes|required|exists:tenancies,id',
            'amount' => 'sometimes|required|numeric',
            'status' => 'nullable|string',
            'stripe_reference' => 'nullable|string',
        ]);
        $payment->update($validated);
        return new PaymentResource($payment);
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->json(['message' => 'Deleted'], 204);
    }
}
