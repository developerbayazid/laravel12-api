<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * LIke and dislike
     */
    public function react(Request $request)
    {
        // Data validator
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer|exists:blog_posts,id',
            'status' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        // User Auth
        $userId = Auth::user()->id;
        $postId = $request->post_id;
        $status = $request->status;

        // If already user leave reaction
        $like = Like::where('user_id', $userId)->where('post_id', $postId)->first();

        if ($like) {
            if ($like->status == $status) {
                // Same reaction again removed
                $like->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Reaction removed!',
                    'data' => $like
                ], 201);
            } else {
                // Reaction update
                $like['status'] = $status;
                $like->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Reaction updated!',
                    'data' => $like
                ], 201);
            }

        } else {
            Like::create([
                'post_id' => $postId,
                'user_id' => $userId,
                'status' => $status
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Reaction added!',
            ], 201);
        }


    }

    /**
     * Like and Dislike count
     */
    public function reactions($postId)
    {
        $post = BlogPost::find($postId);

        if (!$post) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No post found!'
            ], 404);
        }

        $likeCount = Like::where('post_id', $postId)->where('status', 1)->count();
        $disLikeCount = Like::where('post_id', $postId)->where('status', 2)->count();

        return response()->json([
            'status'  => 'success',
            'like'    => $likeCount,
            'dislike' => $disLikeCount,
            'post_id' => $postId,
            'post'    => $post
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
