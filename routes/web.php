<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return Volt::route('livewire.log-dashboard');
})->name('home');
