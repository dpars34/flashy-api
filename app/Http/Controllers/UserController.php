<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\FirebaseStorageService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class UserController extends Controller
{

  public function index() {

  }

  public function show($id) {
    
    try {
      $user = User::findOrFail($id);

      return response()->json([
          'id' => $user->id,
          'name' => $user->name,
          'created_at' => $user->created_at,
          'profile_image' => $user->profile_image,
          'bio' => $user->bio
      ]);
    } catch (ModelNotFoundException $e) {
      return response()->json(['message' => 'User not found'], 404);
    }
    
  }
}
