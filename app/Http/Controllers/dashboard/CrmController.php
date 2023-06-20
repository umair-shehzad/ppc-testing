<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmController extends Controller
{
    public function getCrmData(Request $request)
    {
        try {
            $orders = Order::select('id', 'lead_id', 'price', 'created_at')
                ->with('lead:id,beds,baths,garage,city_id,county_id,state_id,address,zip_code,owner_agent_wholesaler,square_footage,occupancy,ideal_selling_timeframe,mortgage,listed_with_real_estate_agent,repairs_needed,property_condition,how_long_you_owned,year_of_construction,type_of_house,seller_id', 'lead.seller', 'lead.seller.sellerEmails:id,email,seller_id', 'lead.seller.sellerPhones:id,phone,type,seller_id', 'lead.state', 'lead.city')
                ->where('user_id', Auth::id())->paginate(10);
            $response_code = $orders->isNotEmpty() ? 200 : 404;

            if ($orders->isNotEmpty()) {
                storeApiResponse($request->api_request_id, ['message' => 'Crm data fetched!'],  $response_code, Auth::id());
                return response()->success($orders,  $response_code);
            }
            storeApiResponse($request->api_request_id, ['message' => 'Crm data not found!'],  $response_code, Auth::id());
            return response()->error('Crm data not found!',  $response_code);
        } catch (\Exception $e) {
            return throwException('CrmController/getCrmData', $e);
        }
    }
}
