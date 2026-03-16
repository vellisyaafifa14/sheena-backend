<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChannelConnection;

class ChannelConnectionController extends Controller
{
    public function index()
    {
        $connections = ChannelConnection::with('channel')
            ->orderBy('id_connection', 'desc')
            ->get();

        return response()->json([
            'message' => 'Data koneksi channel berhasil diambil',
            'data' => $connections
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_channel' => 'required|exists:channels,id_channel',
            'shop_id' => 'nullable|string|max:255',
            'shop_name' => 'nullable|string|max:255',
            'access_token' => 'nullable|string',
            'refresh_token' => 'nullable|string',
            'status_connection' => 'required|in:connected,disconnected, expired',
        ]);

        $connection = ChannelConnection::create($validated);

        return response()->json([
            'message' => 'Koneksi channel berhasil disimpan',
            'data' => $connection
        ], 201);
    }
}
