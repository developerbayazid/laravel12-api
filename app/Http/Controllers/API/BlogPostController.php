<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Seo;
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
        $posts = BlogPost::with('seo_data')->get();

        return response()->json([
            'status' => 'success',
            'count' => count($posts),
            'data' => $posts
        ], 200);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'user_id'          => 'required|numeric',
            'category_id'      => 'required|numeric',
            'title'            => 'required|unique:blog_posts,title',
            'content'          => 'required',
            'thumbnail'        => 'nullable|image|max:2048',
            'meta_title'       => 'required',
            'meta_description' => 'required',
            'meta_keywords'    => 'required',
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

        if (Auth::user()->role == 'admin'|| Auth::user()->role == 'author') {
            $data['status'] = 'published';
        }

        $blogPost = BlogPost::create($data); // It will create new record in database

        $seoData = [];

        $seoData['post_id'] = $blogPost->id;
        $seoData['meta_title'] = $request->meta_title;
        $seoData['meta_description'] = $request->meta_description;
        $seoData['meta_keywords'] = $request->meta_keywords;

        Seo::create($seoData); // It will create new record in seo table

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
        // Check blog post
        $blogPost = BlogPost::find($id);

        if (!$blogPost) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No blog post found!'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $blogPost
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Check blog post exists or not
        $blogPost = BlogPost::find($id);

        if (!$blogPost) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No blog post found!'
            ], 404);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'user_id'          => 'required|numeric',
            'category_id'      => 'required|numeric',
            'title'            => 'required|unique:blog_posts,title,' . $id,
            'content'          => 'required',
            'thumbnail'        => 'nullable|image|max:2048',
            'meta_title'       => 'required',
            'meta_description' => 'required',
            'meta_keywords'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        // Check if user is logged in
        $loggedInUser = Auth::user();

        // Check if category id is exists in DB
        $category = BlogCategory::find($request->category_id);
        if (!$category) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Category not found!'
            ], 404);
        }

        // Check additional condition to restrict authorized edit
        if ($loggedInUser->id == $request->user_id || Auth::user()->role == 'admin') {

            $blogPost->title = $request->title;
            $blogPost->user_id = $request->user_id;
            $blogPost->category_id = $request->category_id;
            $blogPost->slug = Str::slug($request->title);
            $blogPost->content = $request->content;
            $blogPost->excerpt = $request->excerpt ?? null;

            // If user role is admin or author post status will be published
            if (Auth::user()->role == 'admin' || Auth::user()->role == 'author') {
                $blogPost->status = 'published';
            }

            $blogPost->save(); // It will update record from database

            $seoData = Seo::where('post_id', $blogPost->id)->first();

            $seoData->meta_title = $request->meta_title;
            $seoData->meta_description = $request->meta_description;
            $seoData->meta_keywords = $request->meta_keywords;

            $seoData->save(); // It will update seo data in database

            return response()->json([
                'status' => 'success',
                'message' => 'Blog post Edited successfully!',
                'data' => $blogPost
            ], 201);
        }

         return response()->json([
            'status' => 'fail',
            'message' => 'You are not allow to perform this operation',
        ], 400);

    }

    /**
     * Update the specified post image
     */
    public function blogPostImage(Request $request, $id)
    {
        // Check blog post exists or not
        $blogPost = BlogPost::find($id);

        if (!$blogPost) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No blog post found!'
            ], 404);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|numeric',
            'thumbnail'   => 'nullable|image|max: 2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        // Check if user is logged in
        $loggedInUser = Auth::user();

         // Check additional condition to restrict authorized edit
        if ($loggedInUser->id == $request->user_id || Auth::user()->role == 'admin') {

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

            $blogPost['thumbnail'] = $imagePath;

            $blogPost->save(); // It will update record in database

            return response()->json([
                'status' => 'success',
                'message' => 'Blog post image updated successfully!',
                'data' => $blogPost
            ], 201);
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'You are not allow to perform this operation'
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Check blog post
        $blogPost = BlogPost::find($id);

        if (!$blogPost) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No blog post found!'
            ], 404);
        }

        // Check user authorization
        $loggedInUser = Auth::user();

        // Check additional condition to restrict authorized edit
        if ($loggedInUser->id == $blogPost->user_id || Auth::user()->role == 'admin') {
            BlogPost::destroy($id); // It will delete record in database

            return response()->json([
                'status' => 'success',
                'message' => 'Post deleted successfully!'
            ], 201);
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'You are not allowed to perform this operation'
        ], 400);
    }
}
