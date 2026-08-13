<?php

use Illuminate\Support\Facades\Route;

// Both routes serve the React SPA shell; React Router handles client-side routing.
Route::get('/', function () {
    return view('app');
});

Route::get('/dashboard', function () {
    return view('app');
});
