<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Channel;

class ChannelController extends Controller
{
     public function index()
    {
        $channels = Channel::orderBy('id_channel')->get();

        return response()->json([
            'success' => true,
            'data' => $channels
        ]);
    }
}
