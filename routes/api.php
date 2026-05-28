<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\Api\AuthController;


Route::middleware('auth:sanctum')->post('/login',[AuthController::class, 'login']);
Route::post('/register',[AuthController::class, 'register']);
