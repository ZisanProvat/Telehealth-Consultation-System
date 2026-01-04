<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $feedback = Feedback::create($validated);

        return response()->json([
            'message' => 'Feedback submitted successfully!',
            'feedback' => $feedback
        ], 201);
    }

    public function index()
    {
        $feedbacks = Feedback::latest()->get();
        return response()->json($feedbacks);
    }

    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        return response()->json([
            'message' => 'Feedback deleted successfully!'
        ]);
    }
}
