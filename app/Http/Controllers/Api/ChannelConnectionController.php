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
}
