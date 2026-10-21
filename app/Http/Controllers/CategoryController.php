<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function setCategory(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:daily,season',
        ]);

        // Set the category in the session
        session(['category' => $validated['category']]);

        // Return to the page the user was already viewing. Redirecting to the
        // dashboard forced an expensive dashboard recalculation on every switch.
        return redirect()->back()->with('success', ucfirst($validated['category']) . ' category selected successfully.');
    }

    /**
     * Get the currently selected category.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategory()
    {
        $category = session('category', 'daily'); // Default to 'daily' if not set

        return response()->json([
            'status' => 'success',
            'category' => $category,
        ]);
    }

}
