<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Http\Resources\WebhookResource;
use Illuminate\Http\Request;

class WebhookApiController extends Controller
{
    public function index()
    {
        return WebhookResource::collection(Webhook::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'event' => 'required|string',
        ]);
        $webhook = Webhook::create($validated);
        return new WebhookResource($webhook);
    }

    public function destroy(Webhook $webhook)
    {
        $webhook->delete();
        return response()->json(['message' => 'Deleted'], 204);
    }
}
