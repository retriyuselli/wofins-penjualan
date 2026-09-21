<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Return the authenticated user profile for iOS.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing(['roles']);

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }
}
