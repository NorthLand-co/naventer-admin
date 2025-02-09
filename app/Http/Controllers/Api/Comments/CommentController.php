<?php

namespace App\Http\Controllers\Api\Comments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Comments\StoreCommentRequest;
use App\Http\Requests\Api\Comments\UpdateCommentRequest;
use App\Models\Comment;
use App\Services\Api\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Display a listing of comments with optional pagination.
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->has('model')) {
            $comments = $this->commentService->getPaginatedCommentsForItem($request->model, $request->id, $request->get('per_page', 10));
        } else {
            $comments = $this->commentService->getPaginatedComments($request->get('per_page', 10));
        }

        return response()->json($comments, Response::HTTP_OK);
    }

    /**
     * Store a newly created comment.
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (is_string($data['commentable_id'])) {
            $commentableType = $data['commentable_type'];
            if (class_exists($commentableType)) {
                $commentableModel = app($commentableType);
                $commentable = $commentableModel::where('slug', $data['commentable_id'])->first();
                if ($commentable) {
                    $data['commentable_id'] = $commentable->id;
                } else {
                    return response()->json(['error' => 'Commentable not found'], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json(['error' => 'Invalid commentable type'], Response::HTTP_BAD_REQUEST);
            }
        }

        $comment = $this->commentService->createComment($data);

        return response()->json($comment->load('user'), Response::HTTP_CREATED);
    }

    /**
     * Display a specific comment.
     */
    public function show(Comment $comment): JsonResponse
    {
        $comment->load(['user', 'replies']);

        return response()->json($comment, Response::HTTP_OK);
    }

    /**
     * Update the specified comment.
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $this->commentService->authorizeCommentOwner($comment);

        $updatedComment = $this->commentService->updateComment($comment, $request->validated());

        return response()->json($updatedComment->load('user'), Response::HTTP_OK);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $this->commentService->authorizeCommentOwner($comment);

        $this->commentService->deleteComment($comment);

        return response()->json(['message' => 'Comment deleted successfully'], Response::HTTP_OK);
    }

    /**
     * Increment the upvotes for a comment.
     */
    public function upvote(Comment $comment): JsonResponse
    {
        $upvotes = $this->commentService->incrementUpvotes($comment);

        return response()->json(['upvotes' => $upvotes], Response::HTTP_OK);
    }

    /**
     * Increment the downvotes for a comment.
     */
    public function downvote(Comment $comment): JsonResponse
    {
        $downvotes = $this->commentService->incrementDownvotes($comment);

        return response()->json(['downvotes' => $downvotes], Response::HTTP_OK);
    }
}
