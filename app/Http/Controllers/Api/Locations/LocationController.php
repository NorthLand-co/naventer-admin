<?php

namespace App\Http\Controllers\Api\Locations;

use App\Http\Controllers\Controller;
use App\Http\Resources\Locations\LocationResource;
use App\Models\Location;
use App\Models\UserOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    /**
     * List and filter locations
     */
    public function index(Request $request)
    {
        $query = Location::query();
        $importantCitiesId = [147, 196, 413, 231, 462, 85, 474, 99, 83, 104, 118];

        // Filter by name
        if ($request->has('name')) {
            $name = $request->get('name');
            if (is_null($name)) {
                $cacheKey = 'top_orders_city_list';
                $cacheDuration = now()->addMonth(1);
                $topLocationIdList = Cache::remember($cacheKey, $cacheDuration, function () use ($importantCitiesId) {
                    $locations = UserOrder::query()
                        ->select('user_addresses.city_id')
                        ->join('user_addresses', 'user_orders.user_address_id', '=', 'user_addresses.id')
                        ->selectRaw('count(*) as count')
                        ->groupBy('user_addresses.city_id')
                        ->orderBy('count', 'desc')
                        ->get();

                    $ordersLocation = $locations->pluck('city_id')->toArray();

                    return array_unique(array_merge($importantCitiesId, $ordersLocation));
                });
                $query->whereIn('id', $topLocationIdList);
            } else {
                $query->where('name', 'like', '%'.$name.'%');
            }
        }

        // Filter by division type (enum)
        $query->where('division_type', $request->get('division_type') ?? 1);

        // Filter by parent location
        if ($request->has('parent_country_division_id')) {
            $query->where('parent_country_division_id', $request->get('parent_country_division_id'));
        }

        // Determine if relations should be included based on the 'include' query parameter
        $includes = explode(',', $request->query('include', ''));

        if (in_array('parent', $includes)) {
            $query->with('parent');
        }

        if (in_array('children', $includes)) {
            $query->with('children');
        }

        // Eager load the 'state' relationship if 'state' is included in the request
        if (in_array('state', $includes)) {
            $query->with('parent');
        }

        $query->orderByRaw(
            'CASE id '.implode('', array_map(function ($id, $index) {
                return "WHEN $id THEN $index + 1 ";
            }, $importantCitiesId, array_keys($importantCitiesId))).'ELSE 12 END'
        );

        // Pagination or all results
        if ($request->has('paginate') && $request->get('paginate') == true) {
            return LocationResource::collection($query->paginate());
        } else {
            return LocationResource::collection($query->get());
        }
    }

    /**
     * Get a specific location with its parent and children
     */
    public function show(Location $location)
    {
        return new LocationResource($location->with('parent', 'children'));
    }
}
