<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseStorageService;


class AuthController extends Controller
{
    protected $firebaseStorageService;

    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function registerConfirmation(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'name' => 'required|unique:users',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'bio' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }

        return response()->json((object)[], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'name' => 'required|unique:users',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'bio' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->file('profile_image') != null) {
            // Upload profile image to Firebase Storage
            $profileImageURL = $this->firebaseStorageService->uploadProfileImage($request->file('profile_image'), $request->username);
        } else {
            $profileImageURL = null;
        }

        // Create user
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'profile_image' => $profileImageURL,
            'bio' => $request->bio
        ]);

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    public function editConfirmation(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email,' . $request->user()->id, // Ignore the current user's email
            'name' => 'required|unique:users,name,' . $request->user()->id, // Ignore the current user's name
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'bio' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }

        return response()->json((object)[], 200);
    }

    public function edit(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email,' . $request->user()->id, // Ignore the current user's email
            'name' => 'required|unique:users,name,' . $request->user()->id, // Ignore the current user's name
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'bio' => 'nullable|string'
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        $user = $request->user();
    
        if ($request->has('updateImage') && $request->updateImage == 'true') {
            if ($request->file('profile_image') != null) {
                // If there's an existing image, delete it
                if ($user->profile_image) {
                    $this->firebaseStorageService->deleteProfileImage($user->profile_image);
                }
                // Upload profile image to Firebase Storage
                $profileImageURL = $this->firebaseStorageService->uploadProfileImage($request->file('profile_image'), $user->id);
                $user->profile_image = $profileImageURL;
            } else {
                // If no image is uploaded, remove the current image
                if ($user->profile_image) {
                    $this->firebaseStorageService->deleteProfileImage($user->profile_image);
                }
                $user->profile_image = null;
            }
        }
    
        // Update user details
        $user->email = $request->input('email');
        $user->name = $request->input('name');
        $user->bio = $request->input('bio');
    
        // Save the updated user details
        $user->save();
    
        return response()->json(['message' => 'User details updated successfully', 'user' => $user], 200);
    }

    public function details(Request $request) 
    {
        return response()->json($request->user());
    }

    public function validateOldPassword(Request $request) {
        // Validate the old password input
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        $user = $request->user();
    
        // Check if the old password matches the current password
        if (!Hash::check($request->input('old_password'), $user->password)) {
            return response()->json(['error' => 'Old password is incorrect'], 200);
        }
    
        return response()->json((object)[], 200);
    }

    public function checkPassword(Request $request) {
        // Validate the old password input
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        $user = $request->user();
    
        // Check if the old password matches the current password
        if (!Hash::check($request->input('old_password'), $user->password)) {
            return response()->json(['error' => 'Password is incorrect'], 200);
        }
    
        return response()->json((object)[], 200);
    }

    public function changePassword(Request $request) {
        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:6',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        $user = $request->user();
    
        // Check if the old password matches the current password
        if (!Hash::check($request->input('old_password'), $user->password)) {
            return response()->json(['error' => 'Old password is incorrect'], 400);
        }
    
        // Update the user's password
        $user->password = Hash::make($request->input('new_password'));
        $user->save();
    
        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function deleteAccount(Request $request) {
        // Validate the password input
        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        $user = $request->user();
    
        // Check if the provided password matches the current user's password
        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json(['error' => 'Password is incorrect'], 400);
        }
    
        // Delete related data
        $user->cards()->delete();      // Delete the user's cards
        $user->decks()->delete();      // Delete the user's decks
        $user->highscores()->delete(); // Delete the user's highscores
        $user->likes()->delete();      // Delete the user's likes
    
        // Finally, delete the user account
        $user->delete();
    
        // Return a success response
        return response()->json(['message' => 'Your account and all related data have been deleted successfully'], 200);
    }


}
