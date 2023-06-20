<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use App\Libraries\Stripe;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StripeController extends Controller
{
    public function makePayment(Request $request)
    {
        try {
            $user = User::where('id', Auth::id())->first();
            $data = [
                'api_request_id' => $request->api_request_id,
                'user_email' => $user->email,
                'user_first_name' => $user->first_name,
                'user_last_name' => $user->last_name,
                'stripe_token' => $request->stripe_token,
                'amount' => intval($request->amount),
                'lead_id' => intval($request->lead_id),
                'user_id' => $user->id,
                'discount' => null,
                'coupon_code' => null
            ];
            $stripe_response = Stripe::makePayment($data);
            if ($stripe_response['charge']->status == "succeeded") {
                $lead = Lead::where('id', $data['lead_id'])->first();
                $lead->status = 'sold';
                $lead->save();
                $order_status = 'completed';
                $transaction_status = 'completed';
                $receipt_url = $stripe_response['charge']->receipt_url;
            } else {
                $order_status = 'failed';
                $transaction_status = 'failed';
                $receipt_url = null;
            }
            $order_status = $stripe_response['charge']->status == "succeeded" ? 'completed' : 'failed';
            $order_details = [
                'lead_id' => $data['lead_id'],
                'user_id' => $data['user_id'],
                'price' => $data['amount'],
                'discount' => $data['discount'],
                'coupon_code' => $data['coupon_code'],
                'status' => $order_status
            ];
            $order = createOrder($order_details);
            $transaction_details = [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'pg_transaction_id' => $stripe_response['charge']->balance_transaction,
                'amount' => $stripe_response['charge']->amount_captured,
                'currency' => config('constants.STRIPE_CURRENCY'),
                'payment_gateway' => 'Stripe',
                'status' => $transaction_status,
                'stripe_customer_id' => $stripe_response['charge']->customer,
                'receipt_url' =>  $receipt_url,
                'failure_reason' => $stripe_response['charge']->failure ?? null,
            ];
            createTransaction($transaction_details);
            if ($stripe_response['charge']->status == "succeeded") {
                $response = ['message' => 'Lead bought successfully!'];
                storeApiResponse($request->api_request_id, $response, 200, Auth::id());
                return response()->success($response, 200);
            }
            $response = 'Some error occured while making transaction!';
            storeApiResponse($request->api_request_id, ['message' => $response], 400, Auth::id());
            return response()->error($response, 400);
        } catch (\Exception $e) {
            return throwException('StripeController/makePayment', $e);
        }
    }
}
