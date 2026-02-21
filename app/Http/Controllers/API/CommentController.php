<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = Comment::get();

        return response()->json([
            'status' => 'success',
            'count' => count($comments),
            'data' => $comments
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer|exists:blog_posts,id',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        $data['post_id'] = $request->post_id;
        $data['user_id'] = Auth::user()->id;
        $data['content'] = $request->content;

        if (Auth::user()->role == 'admin') {
            $data['status'] = 'approved';
        }

        Comment::create($data); // It will create record in database table

        return response()->json([
            'status' => 'success',
            'message' => 'Comment created and waiting for admin approval',
            'data' => $data
        ], 201);
    }

    /**
     * Change Comment status
     */
    public function changePostStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required|exists:comments,id',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        $comment = Comment::find($request->comment_id);
        $comment['status'] = $request->status;
        $comment->save(); // It will update record in database

        return response()->json([
            'status' => 'Success',
            'message' => 'Status updated',
            'data' => $comment
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = BlogPost::find($id);

        if (!$post) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid post id'
            ], 404);
        }

        $comments = Comment::where('post_id', $id)->where('status', 'approved')->get();

        return response()->json([
            'status' => 'success',
            'count' => count($comments),
            'post' => $post,
            'comments' => $comments
        ], 200);
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
