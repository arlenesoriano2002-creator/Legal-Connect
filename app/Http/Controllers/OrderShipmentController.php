<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\OrderShipped;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Resend\Laravel\Facades\Resend;

class OrderShipmentController extends Controller
{
    /**
     * Ship the given order.
     */
    public function store(Request $request): RedirectResponse
    {
        $order = Order::findOrFail($request->order_id);

        // Ship the order...

        Resend::emails()->send([
            'from' => 'Acme <legalconnect681@gmail.com>',
            'to' => [$request->user()->email],
            'subject' => 'hello world',
            'html' => (new OrderShipped($order))->render(),
        ]);

        return redirect('/orders');
    }
}