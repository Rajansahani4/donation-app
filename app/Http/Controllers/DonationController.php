<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;



class DonationController extends Controller
{
    public function showForm()
    {
        return view('donate');
    }

    public function createCheckoutSession(Request $request)
    {
        // Load Stripe credentials from config
        Stripe::setApiKey(config('stripe.stripe.secret'));

        $donation = $request->all();
        $amount = intval(($donation['totalAmount']) * 100); // Convert to cents

        $session = Session::create([
            'payment_method_types' => ['card'],
            'customer_email' => $donation['donorEmail'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $donation['selectedProject'],
                        'description' => $donation['message'] ?? 'Donation',
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => config('app.url') . '/thank-you',
            'cancel_url' => config('app.url') . '/donation-cancelled',
            'metadata' => [
                'donor_name'     => $donation['donorName'],
                'donation_type'  => $donation['donationType'],
                'anonymous'      => $donation['stayAnonymous'] ? 'yes' : 'no',
                'allow_contact'  => $donation['allowContact'] ? 'yes' : 'no',
            ],
        ]);

        return response()->json(['id' => $session->id]);
    }


    public function thankYou(Request $request)
    {
        return view('thank-you');
    }


}
