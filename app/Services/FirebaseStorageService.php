<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage as LocalStorage;

class FirebaseStorageService
{
    private $firebaseStorage;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
        $this->firebaseStorage = $factory->createStorage();
    }

    public function uploadProfileImage($file, $userId)
    {
        // Save the profile image to a temporary location
        $filePath = $file->store('temp');
        $localPath = LocalStorage::path($filePath);

        // Upload the file to Firebase Storage
        $bucket = $this->firebaseStorage->getBucket();
        $firebaseStoragePath = "profile_images/{$userId}/" . basename($filePath);
        $bucket->upload(fopen($localPath, 'r'), [
            'name' => $firebaseStoragePath
        ]);

        // Get the image URL
        $imageReference = $bucket->object($firebaseStoragePath);
        $profileImageURL = $imageReference->signedUrl(new \DateTime('+1 year'));

        // Delete the temporary file
        LocalStorage::delete($filePath);

        return $profileImageURL;
    }
}