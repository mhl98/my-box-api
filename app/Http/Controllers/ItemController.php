<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Utility\ApiResponse;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'box_id' => 'required|exists:boxes,id',
            'text1' => 'required|string|max:255',
            'text2' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 400, $validator->errors());
        }

        $box = Box::where('id', $request->box_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$box) {
            return ApiResponse::error('You do not have permission to add items to this box', 403);
        }

        $item = Item::create([
            'box_id' => $box->id,
            'text1' => $request->text1,
            'text2' => $request->text2,
        ]);

        return ApiResponse::success($item, 'Item added successfully to the box', 201);
    }

    public function show(Request $request, string $id)
    {
        $item = Item::with('box')->find($id);

        if (!$item) {
            return ApiResponse::error('Item not found', 404);
        }

        if ($item->box->user_id !== $request->user()->id) {
            return ApiResponse::error('You do not have permission to view this item', 403);
        }

        return ApiResponse::success($item, 'Item retrieved successfully');
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'text1' => 'sometimes|required|string|max:255',
            'text2' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 400, $validator->errors());
        }

        $item = Item::with('box')->find($id);

        if (!$item) {
            return ApiResponse::error('Item not found', 404);
        }

        if ($item->box->user_id !== $request->user()->id) {
            return ApiResponse::error('You do not have permission to update this item', 403);
        }

        $item->update($request->only(['text1', 'text2']));

        return ApiResponse::success($item, 'Item updated successfully');
    }

    public function destroy(Request $request, string $id)
    {
        $item = Item::with('box')->find($id);

        if (!$item) {
            return ApiResponse::error('Item not found', 404);
        }

        if ($item->box->user_id !== $request->user()->id) {
            return ApiResponse::error('You do not have permission to delete this item', 403);
        }

        $item->delete();

        return ApiResponse::success(null, 'Item deleted successfully');
    }

    public function level(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'is_true' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 400, $validator->errors());
        }

        $item = Item::with('box')->find($id);

        if (!$item) {
            return ApiResponse::error('Item not found', 404);
        }

        if ($item->box->user_id !== $request->user()->id) {
            return ApiResponse::error('You do not have permission to update this item', 403);
        }

        $currentLevel = $item->level;
        $isTrue = $request->is_true;

        $rules = [
            1 => ['correct' => 2, 'incorrect' => 1, 'correct_interval' => ['method' => 'addDays', 'value' => 3], 'incorrect_interval' => ['method' => 'addDay', 'value' => 1]],
            2 => ['correct' => 3, 'incorrect' => 1, 'correct_interval' => ['method' => 'addWeeks', 'value' => 1], 'incorrect_interval' => ['method' => 'addDay', 'value' => 1]],
            3 => ['correct' => 4, 'incorrect' => 1, 'correct_interval' => ['method' => 'addWeeks', 'value' => 2], 'incorrect_interval' => ['method' => 'addDay', 'value' => 1]],
            4 => ['correct' => 5, 'incorrect' => 1, 'correct_interval' => ['method' => 'addMonths', 'value' => 1], 'incorrect_interval' => ['method' => 'addDay', 'value' => 1]],
            5 => ['correct' => 5, 'incorrect' => 1, 'correct_interval' => ['method' => 'addMonths', 'value' => 1], 'incorrect_interval' => ['method' => 'addDay', 'value' => 1]],
            6 => ['correct' => 6, 'incorrect' => 1, 'correct_interval' => ['method' => 'addMonths', 'value' => 1], 'incorrect_interval' => ['method' => 'addDay', 'value' => 1]],
        ];

        if (!array_key_exists($currentLevel, $rules)) {
            return ApiResponse::error('Invalid current level', 400);
        }

        $rule = $rules[$currentLevel];
        $intervalKey = $isTrue ? 'correct_interval' : 'incorrect_interval';
        $newLevel = $isTrue ? $rule['correct'] : $rule['incorrect'];
        $interval = $rule[$intervalKey];

        $showDate = now();
        $showDate->{$interval['method']}($interval['value']);

        $item->update([
            'level' => $newLevel,
            'show_date' => $showDate,
        ]);

        return ApiResponse::success($item, 'Item level updated successfully');
    }
}
