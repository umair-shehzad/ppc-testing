<?php

namespace App\Http\Controllers\dashboard;

use App\Filters\Leads\Baths;
use App\Filters\Leads\Beds;
use App\Filters\Leads\HouseType;
use App\Filters\Leads\HowLongOwned;
use App\Filters\Leads\ListedWithAgent;
use App\Filters\Leads\Mortgage;
use App\Filters\Leads\OwnerWholeSaler;
use App\Filters\Leads\PropertyCondition;
use App\Filters\Leads\RepairsNeeded;
use App\Filters\Leads\SearchCity;
use App\Filters\Leads\SearchCounty;
use App\Filters\Leads\SearchState;
use App\Filters\Leads\SellingTime;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\County;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;

class MainMenuController extends Controller
{
    public function getLeads(Request $request)
    {
        try {
            $leads = app(Pipeline::class)
                ->send(Lead::query())
                ->through([
                    ListedWithAgent::class,
                    SellingTime::class,
                    OwnerWholeSaler::class,
                    RepairsNeeded::class,
                    HowLongOwned::class,
                    Baths::class,
                    Beds::class,
                    PropertyCondition::class,
                    HouseType::class,
                    Mortgage::class,
                    SearchState::class,
                    SearchCity::class,
                    SearchCounty::class,
                ])
                ->thenReturn()
                ->select('id', 'beds', 'baths', 'city_id', 'county_id', 'state_id', 'address', 'zip_code', 'description', 'owner_wholesaler', 'price', 'square_footage', 'occupancy', 'ideal_selling_timeframe', 'motivation', 'mortgage', 'listed_with_real_estate_agent', 'repairs_needed', 'property_condition', 'how_long_you_owned', 'year_of_construction', 'type_of_house', 'status', 'seller_id', 'created_at')
                ->where('status', '!=', 'sold')
                ->paginate(10);

            $leads->load(['seller:id,full_name', 'state', 'county', 'city']);
            $leads->loadCount(['orders']);

            if ($leads->isNotEmpty()) {
                foreach ($leads as $key => $lead) {
                    $lead['div_id'] = 'div' . ($key + 1);
                    $additional_data = [
                        'hide' => 'Hide',
                        'more_details' => 'More Details',
                        'more_details_icon' => env('APP_URL') . '/storage/other_icons/more_detail_icon.png',
                        'hide_icon' => env('APP_URL') . '/storage/other_icons/hide_icon.png',
                    ];
                    $lead['additional_data'] = $additional_data;
                }
                storeApiResponse($request->api_request_id, ['message' => 'Leads fetched!'], 200, Auth::id());
                return response()->success($leads, 200);
            }
            storeApiResponse($request->api_request_id, ['message' => 'Leads not found!'], 404, Auth::id());
            return response()->error('Leads not found!', 404);
        } catch (\Exception $e) {
           
            return throwException('MainMenuController/getLeads',$e);
        }
    }

    public function getCountiesDropdown(Request $request)
    {
        try {
            $user_id = Auth::id() ?? null;
            $stateName = $request->input('state_name');
            $counties = County::when($stateName, function ($query) use ($stateName) {
                $query->whereHas('state', function ($subQuery) use ($stateName) {
                    $subQuery->where('name', $stateName);
                });
            })
                ->get();
            if ($counties->isNotEmpty()) {
                storeApiResponse($request->api_request_id, ['message' => 'Counties fetched!'], 200, $user_id);
                return response()->success($counties, 200);
            }
            storeApiResponse($request->api_request_id, ['message' => 'Counties not found!'], 404, $user_id);
            return response()->error('Counties not found!', 404);
        } catch (\Exception $e) {
            return throwException('MainMenuController/getCountiesDropdown', $e);
        }
    }

    public function getcitiesDropdown(Request $request)
    {
        try {
            $user_id = Auth::id() ?? null;
            $countyId = $request->input('county_id');
            $cities = City::when($countyId, function ($query) use ($countyId) {
                return $query->where('county_id', $countyId);
            })->get();
            if ($cities->isNotEmpty()) {
                storeApiResponse($request->api_request_id, ['message' => 'Cities fetched!'], 200, $user_id);
                return response()->success($cities, 200);
            }
            storeApiResponse($request->api_request_id, ['message' => 'Cities not found!'], 404, $user_id);
            return response()->error('Cities not found!', 404);
        } catch (\Exception $e) {
            return throwException('MainMenuController/getcitiesDropdown', $e);
        }
    }
}
