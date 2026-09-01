<?php

namespace App\Http\Controllers;

use App\Models\StreamSource;
use App\Support\PlaybackPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaybackPolicyController extends Controller
{
    public function show(Request $request, StreamSource $stream): JsonResponse
    {
        return response()->json(PlaybackPolicy::evaluate($stream, $request));
    }
}
