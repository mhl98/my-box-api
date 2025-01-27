<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use App\Utility\ApiResponse;
use App\Utility\ApiResponseWithPaginator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::paginate(10);

        return new ApiResponseWithPaginator(
            PostResource::collection($posts),
            [
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 400, $validator->errors());
        }

        $post = $request->user()->posts()->create($request->all());

        return ApiResponse::success(
            new PostResource($post),
            'Post created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return ApiResponse::error('Post not found', 404);
        }

        return ApiResponse::success(new PostResource($post), 'Post retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        try {
            Gate::authorize('modify', $post);
        } catch (AuthorizationException $e) {
            return ApiResponse::error('Authorization failed', 403, $e->getMessage());
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 400, $validator->errors());
        }

        $post->update($request->all());

        return ApiResponse::success(
            new PostResource($post),
            'Post updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        try {
            Gate::authorize('modify', $post);
        } catch (AuthorizationException $e) {
            return ApiResponse::error('Authorization failed', 403, $e->getMessage());
        }

        $post->delete();

        return ApiResponse::success(null, 'Post deleted successfully');
    }
}
