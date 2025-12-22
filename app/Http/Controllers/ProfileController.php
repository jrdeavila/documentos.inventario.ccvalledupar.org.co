<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(
            new UserResource(Auth::user()),
            Response::HTTP_OK
        );
    }
}
