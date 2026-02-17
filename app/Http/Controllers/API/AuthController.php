<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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


        //Image Upload
        $imagePath = null;

        if ($request->hasFile('profile_picture') && $request->file('profile_picture')->isValid()) {
            $file = $request->file('profile_picture');

            //Generate a unique file name
            $fileName = time() . '_' . $file->getClientOriginalName();

            //Move file to the public directory
            $file->move(public_path('storage/profile'), $fileName);

            //save the relative path to the database
            $imagePath = 'storage/profile/' . $fileName;

        }

        $data['profile_picture'] = $imagePath;


        User::create($data); //It will store data in users table

        return response()->json([
            'status'  => 'success',
            'message' => 'New user created successfully!'
        ], 201);
    }

    //Login API
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            $response['token'] = $user->createToken('BlogApp')->plainTextToken;
            $response['email'] = $user->email;
            $response['name'] = $user->name;

            return response()->json([
                'status' => 'success',
                'message' => 'User logged in successfully!',
                'response' => $response
            ], 200);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid Credential'
            ], 400);
        }
    }

    //Profile API
    public function profile()
    {
        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'data' => $user
        ], 200);

    }

    public function logout()
    {
        $user = Auth::user();
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully!'
        ], 200);
    }


}
