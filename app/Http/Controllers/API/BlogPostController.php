<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|numeric',
            'category_id' => 'required|numeric',
            'title'       => 'required|unique:blog_posts,title',
            'content'     => 'required',
            'thumbnail'   => 'nullable|image|max: 2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        // Check if user is same as logged in user
        $loggedInUser = Auth::user();
        if ($loggedInUser->id != $request->user_id) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Un-authorized user'
            ], 400);
        }

        // Check if category id is exists in DB
        $category = BlogCategory::find($request->category_id);
        if (!$category) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Category not found!'
            ], 404);
        }

        $imagePath = null;
        if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
            $file = $request->file('thumbnail');

            // Generate unique file name
            $fileName = time() . '-' . $file->getClientOriginalName();

            // Move file into storage
            $file->move(public_path('storage/posts/'), $fileName);

            // Save image path into our database
            $imagePath = 'storage/posts/' . $fileName;
        }

        $data['title'] = $request->title;
        $data['slug'] = Str::slug($request->title);
        $data['user_id'] = $request->user_id;
        $data['category_id'] = $request->category_id;
        $data['content'] = $request->content;
        $data['excerpt'] = $request->excerpt ?? null;
        $data['thumbnail'] = $imagePath ?? null;
        $data['published_at'] = date('Y-m-d, H:i:s');

        if (Auth::user()->role == 'admin') {
            $data['status'] = 'published';
        }

        BlogPost::create($data); // It will create new blog post in database

        return response()->json([
            'status' => 'success',
            'message' => 'Blog post created successfully!',
            'data' => $data
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
