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

    // Add this method to your ItemController
    public function level(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'is_true' => 'required|boolean',
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

        if ($item->box->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update this item',
            ], 403);
        }

        $currentLevel = $item->level;
        $isTrue = $request->is_true;

        $rules = [
            1 => [
                'correct' => 2,
                'incorrect' => 1,
                'correct_interval' => ['method' => 'addDays', 'value' => 3],
                'incorrect_interval' => ['method' => 'addDay', 'value' => 1]
            ],
            2 => [
                'correct' => 3,
                'incorrect' => 1,
                'correct_interval' => ['method' => 'addWeeks', 'value' => 1],
                'incorrect_interval' => ['method' => 'addDay', 'value' => 1]
            ],
            3 => [
                'correct' => 4,
                'incorrect' => 1,
                'correct_interval' => ['method' => 'addWeeks', 'value' => 2],
                'incorrect_interval' => ['method' => 'addDay', 'value' => 1]
            ],
            4 => [
                'correct' => 5,
                'incorrect' => 1,
                'correct_interval' => ['method' => 'addMonths', 'value' => 1],
                'incorrect_interval' => ['method' => 'addDay', 'value' => 1]
            ],
            5 => [
                'correct' => 5,
                'incorrect' => 1,
                'correct_interval' => ['method' => 'addMonths', 'value' => 1],
                'incorrect_interval' => ['method' => 'addDay', 'value' => 1]
            ],
        ];

        if (!array_key_exists($currentLevel, $rules)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid current level',
            ], 400);
        }

        $rule = $rules[$currentLevel];
        $intervalKey = $isTrue ? 'correct_interval' : 'incorrect_interval';
        $newLevel = $isTrue ? $rule['correct'] : $rule['incorrect'];
        $interval = $rule[$intervalKey];

        // Calculate new show date
        $showDate = now();
        $showDate->{$interval['method']}($interval['value']);

        $item->update([
            'level' => $newLevel,
            'show_date' => $showDate
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Item level updated successfully',
            'data' => $item,
        ], 200);
    }
}
