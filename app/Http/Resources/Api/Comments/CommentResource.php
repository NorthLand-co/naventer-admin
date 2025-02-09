<?php

namespace App\Http\Resources\Api\Comments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->user ? $this->user->name : $this->guest_name,
            'avatar' => $this->user ? $this->user->avatar : null,
            'score' => $this->score,
            'title' => $this->title,
            'content' => $this->content,
            'upvotes' => $this->upvotes,
            'downvotes' => $this->downvotes,
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
        ];
    }
}
