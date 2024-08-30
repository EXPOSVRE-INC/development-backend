<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReject;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function requestRefund(Request $request) {
        $order = Order::where(['id' => $request->get('orderId')])->first();

        if ($order->buyer->id == auth('api')->user()->id) {

            $refund = ($order->reject) ? $order->reject : new OrderReject();

            $refund->requestMessage = $request->get('message');
            $refund->order_id = $order->id;

            $refund->save();

            return response()->json(['data' => 'success!']);
        } else {
            return response()->json(['data' => 'Forbidden!'], 401);
        }

    }

    public function responseRefund(Request $request) {
        $order = Order::where(['id' => $request->get('orderId')])->first();
        if ($order->seller->id == auth('api')->user()->id) {
            $refund = ($order->reject) ? $order->reject : new OrderReject();

            $refund->responseMessage = $request->get('message');
            $refund->order_id = $order->id;
            $refund->save();

            return response()->json(['data' => 'success!']);
        } else {
            return response()->json(['data' => 'Forbidden!'], 401);
        }
    }
}
