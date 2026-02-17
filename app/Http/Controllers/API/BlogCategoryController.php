<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = BlogCategory::get();

        return response()->json([
            'status' => 'success',
            'count' => count($categories),
            'data' => $categories
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:blog_categories,name'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        $data = null;

        $data['name'] = $request->name;
        $data['slug'] = Str::slug($request->name);

        BlogCategory::create($data); // Create new record in database table

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully!',
            'data' => $data
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = BlogCategory::find($id);


        if ($category) {
            return response()->json([
                'status' => 'success',
                'data' => $category
            ], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'Category not found!'
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:blog_categories,name,' . $id
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        $category = BlogCategory::find($id);

        if ($category) {
            $category['name'] = $request->name;
            $category['slug'] = Str::slug($request->name);

            $category->save(); // Update to the database table

            return response()->json([
                'status' => 'success',
                'message' => 'Category edited successfully!',
                'data' => $category
            ], 201);
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'Category not found!'
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = BlogCategory::find(($id));

        if ($category) {
            BlogCategory::destroy($id); // It will delete data from our database

            return response()->json([
                'status' => 'success',
                'message' => 'Category deleted successfully!',
            ], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'Category not found!'
        ], 404);
    }
}
