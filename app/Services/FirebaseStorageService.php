<?php

namespace App\Services;

use Illuminate\Http\File;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;
use Illuminate\Support\Facades\Log;
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

    public function deleteProfileImage($filePath)
    {
        function getStoragePathFromURL($url) {
            // Parse the URL to get the path component
            $parsedUrl = parse_url($url, PHP_URL_PATH);
            
            // Find the position of '/profile_images' in the path
            $position = strpos($parsedUrl, '/profile_images');
        
            // Extract the internal storage path starting from '/profile_images'
            $storagePath = substr($parsedUrl, $position + 1);
        
            return $storagePath;
        }

        $bucket = $this->firebaseStorage->getBucket();
        $storagePath = getStoragePathFromURL($filePath);
        Log::info('Attempting to delete file: ' . $storagePath);
        $object = $bucket->object($storagePath);
    
        if ($object->exists()) {
            $object->delete();
            Log::info('File deleted successfully: ' . $storagePath);
        } else {
            Log::warning('File does not exist: ' . $storagePath);
        }
    }
}