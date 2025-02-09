<?php

namespace App\Filament\Resources\RedirectResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class RedirectTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            $this->source_url => [
                'path' => $this->destination_url,
                'options' => [
                    'redirectCode' => $this->redirect_code,
                ],
            ]];
    }
}
