<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function upload(Request $request)
    {
        // Mock successful upload for demo purposes
        return response()->json(['message' => 'Photo uploaded successfully'], 201);
    }
}
