<?php

use App\Livewire\LogDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', LogDashboard::class)->name('home');
