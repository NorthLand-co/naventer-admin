<?php

namespace App\Http\Resources\Locations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Parse the 'include' parameter from the request
        $includes = explode(',', $request->query('include', ''));

        $includeParent = in_array('parent', $includes);
        $includeChildren = in_array('children', $includes);
        $includeState = in_array('state', $includes);

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'division_type' => $this->division_type->label(),
        ];

        if ($includeParent) {
            $data['parent'] = $this->when(
                $this->resource->relationLoaded('parent'),
                function () {
                    return new LocationResource($this->parent->withoutRelations());
                }
            );
        }

        if ($includeChildren) {
            $data['children'] = $this->when(
                $this->resource->relationLoaded('children'),
                function () {
                    return LocationResource::collection($this->children->map->withoutRelations());
                }
            );
        }

        if ($includeState) {
            $state = $this->findHighestStateAncestor();
            if ($state) {
                $data['state'] = new LocationResource($this->findHighestStateAncestor());
            }
        }

        return $data;
    }
}
