<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //Registration API
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'required',
            'email'                 => 'required|unique:users,email',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        $data = $request->all();

        User::create($data); //It will store data in users table

        return response()->json([
            'status'  => 'success',
            'message' => 'New user created successfully!'
        ], 201);
    }
}
