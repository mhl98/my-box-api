<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'box_id' => 'required|exists:boxes,id',
            'text1' => 'required|string|max:255',
            'text2' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $box = Box::where('id', $request->box_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$box) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to add items to this box',
            ], 403);
        }

        $item = Item::create([
            'box_id' => $box->id,
            'text1' => $request->text1,
            'text2' => $request->text2,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Item added successfully to the box',
            'data' => $item,
        ], 201);
    }

    /**
     * Display the specified resource.
     */

    public function show(Request $request, string $id)
    {
        $item = Item::with('box')->find($id);

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found',
            ], 404);
        }

        // Check if the item's box is owned by the user
        if ($item->box->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to view this item',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Item retrieved successfully',
            'data' => $item,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'text1' => 'sometimes|required|string|max:255',
            'text2' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $item = Item::with('box')->find($id);

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found',
            ], 404);
        }

        // Check ownership
        if ($item->box->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update this item',
            ], 403);
        }

        $item->update($request->only(['text1', 'text2']));

        return response()->json([
            'status' => 'success',
            'message' => 'Item updated successfully',
            'data' => $item,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $item = Item::with('box')->find($id);

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found',
            ], 404);
        }

        // Check ownership
        if ($item->box->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete this item',
            ], 403);
        }

        $item->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Item deleted successfully',
        ], 200);
    }
}
