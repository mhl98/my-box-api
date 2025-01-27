<?php

namespace App\Http\Controllers;

use App\Models\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Utility\ApiResponse;
use App\Utility\ApiResponseWithPaginator;

class BoxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $boxes = Box::where('user_id', $user->id)->paginate(10);

        return new ApiResponseWithPaginator(
            $boxes,
            [
                'total_items' => $boxes->total(),
                'total_pages' => $boxes->lastPage(),
            ],
            null,
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                'Validation failed',
                400,
                $validator->errors()
            );
        }

        $box = $request->user()->boxes()->create($request->all());

        return ApiResponse::success(
            $box,
            'Box created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $box = Box::find($id);

        if (!$box) {
            return ApiResponse::error(
                'Box not found',
                404
            );
        }

        if ($box->user_id !== $request->user()->id) {
            return ApiResponse::error(
                'You do not have permission to view this box',
                403
            );
        }

        $box->load(['items' => function ($query) use ($request) {
            if (!$request->has('type') || $request->type !== 'all') {
                $query->whereDate('show_date', '<=', now())
                    ->where('level', '!=', 6);
            }
        }]);

        return ApiResponse::success(
            ['box' => $box],
            'Box retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $box = Box::find($id);

        if (!$box) {
            return ApiResponse::error(
                'Box not found',
                404
            );
        }

        if ($box->user_id !== $request->user()->id) {
            return ApiResponse::error(
                'You do not have permission to update this box',
                403
            );
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                'Validation failed',
                400,
                $validator->errors()
            );
        }

        $box->update($request->only(['title', 'description']));

        return ApiResponse::success(
            $box,
            'Box updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $box = Box::find($id);

        if (!$box) {
            return ApiResponse::error(
                'Box not found',
                404
            );
        }

        if ($box->user_id !== $request->user()->id) {
            return ApiResponse::error(
                'You do not have permission to delete this box',
                403
            );
        }

        $box->delete();

        return ApiResponse::success(
            null,
            'Box deleted successfully'
        );
    }
}
