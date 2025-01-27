<?php

namespace App\Http\Controllers;

use App\Models\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class BoxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $user = $request->user();
        $boxes = Box::where('user_id', $user->id)->paginate(10);
        return response()->json([
            'status' => 'success',
            'message' => 'Boxes retrieved successfully',
            'data' => $boxes
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'All fields are required',
                'error' => $validator->errors()
            ], 400);
        }

        $box = $request->user()->boxes()->create($request->all());


        return response()->json([
            'status' => 'success',
            'message' => 'Box created successfully',
            'data' =>  $box
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $box = Box::with('items')->find($id);

        if (!$box) {
            return response()->json([
                'status' => 'error',
                'message' => 'Box not found',
            ], 404);
        }

        if ($box->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to view this box',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Box retrieved successfully',
            'data' => [
                'box' => $box,

            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $box = Box::find($id);

        if (!$box) {
            return response()->json([
                'status' => 'error',
                'message' => 'Box not found',
            ], 404);
        }

        if ($box->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update this box',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'error' => $validator->errors(),
            ], 400);
        }

        $box->update($request->only(['title', 'description']));

        return response()->json([
            'status' => 'success',
            'message' => 'Box updated successfully',
            'data' => $box,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

        $box = Box::find($id);
        if (!$box) {
            return response()->json([
                'status' => 'error',
                'message' => 'Box not found',
            ], 404);
        }

        if ($box->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete this box',
            ], 403);
        }

        $box->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Box deleted successfully',
        ], 200);
    }
}
