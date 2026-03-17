<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactInfo;

class ContactInfoController extends Controller
{
    public function index()
    {
        $contactInfos = ContactInfo::orderBy('id_contact', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $contactInfos
        ]);
    }
}