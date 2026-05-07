<?php

namespace App\Http\Controllers;

use App\Models\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientAuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
        ]);

        $plainSecret = Str::random(48);

        $client = ApiClient::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'client_secret' => Hash::make($plainSecret),
            'status' => 'active', // or 'pending' if you want approval flow
        ]);

        return response()->json([
            'client_id' => $client->client_id,
            'client_secret' => $plainSecret, // ONLY shown once
        ]);
    }

    public function token(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        $client = ApiClient::where('client_id', $validated['client_id'])->first();

        if (! $client) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (! Hash::check($validated['client_secret'], $client->client_secret)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (! $client->isActive()) {
            return response()->json(['message' => 'Client inactive'], 403);
        }

        // revoke old tokens (optional but recommended)
        $client->tokens()->delete();

        $token = $client->createToken('api-token')->plainTextToken;

        $client->update(['last_used_at' => now()]);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}