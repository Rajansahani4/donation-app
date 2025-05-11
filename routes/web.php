<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonationController;


Route::get('/', [DonationController::class, 'showForm'])->name('donate.show');



Route::post('/create-checkout-session', [DonationController::class, 'createCheckoutSession']);

// Donation Success Page (Thank You)
Route::get('/thank-you', [DonationController::class, 'thankYou'])->name('thank-you');


