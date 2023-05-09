<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Events\NewChatMessage;

class MessageController extends Controller
{
    public function broadcast(Request $request) {
        if(! $request->filled('message')) {
            return response()->json([
                'message' => 'No message provided'
            ], 422);
        }

        event(new NewChatMessage($request->message, $request->roomId));

        return response()->json([], 200);
    }
}
