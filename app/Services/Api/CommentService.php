<?php

namespace App\Services\Api;

use App\Http\Resources\Api\Comments\CommentResource;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CommentService
{
    protected $commentableTypeMap = [
        'product' => Product::class,
        // 'post' => \App\Models\Post::class,
    ];

    public function getPaginatedComments(int $perPage)
    {
        $comments = Comment::with(['user', 'replies'])->latest()->paginate($perPage);

        return CommentResource::collection($comments);
    }

    public function getPaginatedCommentsForItem(string $commentableType, int $commentableId, int $perPage)
    {
        $commentableClass = $this->resolveCommentableType($commentableType);

        $comments = Comment::with(['user', 'replies'])
            ->where('commentable_type', $commentableClass)
            ->where('commentable_id', $commentableId)
            ->latest()
            ->paginate($perPage);

        return CommentResource::collection($comments);
    }

    protected function resolveCommentableType(string $commentableType): string
    {
        if (! array_key_exists($commentableType, $this->commentableTypeMap)) {
            throw new \InvalidArgumentException("Invalid commentable type: {$commentableType}");
        }

        return $this->commentableTypeMap[$commentableType];
    }

    public function createComment(array $data): Comment
    {
        $data['user_id'] = Auth::id();

        return Comment::create($data);
    }

    public function updateComment(Comment $comment, array $data): Comment
    {
        $comment->update($data);

        return $comment;
    }

    public function deleteComment(Comment $comment): void
    {
        $comment->delete();
    }

    public function authorizeCommentOwner(Comment $comment): void
    {
        if ($comment->user_id && $comment->user_id !== Auth::id()) {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized action.');
        }
    }

    public function incrementUpvotes(Comment $comment): int
    {
        $comment->increment('upvotes');

        return $comment->upvotes;
    }

    public function incrementDownvotes(Comment $comment): int
    {
        $comment->increment('downvotes');

        return $comment->downvotes;
    }
}
