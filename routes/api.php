<?php

use App\Http\Controllers\Api\TodoController;
use Illuminate\Support\Facades\Route;

Route::post('/todos', [TodoController::class, 'store']);