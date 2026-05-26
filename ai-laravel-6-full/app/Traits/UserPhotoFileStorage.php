<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UserPhotoFileStorage
{
    public function storeUserPhoto(?UploadedFile $uploadedFile, User $user): ?string
    {
        if ($uploadedFile) {
            $path = basename(Storage::disk('public')->putFile('photos', $uploadedFile));
            $user->photo_url = $path;
            $user->save();
            return $path;
        }
        return null;
    }

    public function deleteUserPhoto(User $user): bool
    {
        if ($user->photo_url) {
            if (Storage::disk('public')->exists('photos/' . $user->photo_url)) {
                Storage::disk('public')->delete('photos/' . $user->photo_url);
                $user->photo_url = null;
                $user->save();
                return true;
            }
            $user->photo_url = null;
            $user->save();
        }
        return false;
    }

    public function deletePhotoFile(?string $photo_url): bool
    {
        if ($photo_url !== null) {
            if (Storage::disk('public')->exists('photos/' . $photo_url)) {
                Storage::disk('public')->delete('photos/' . $photo_url);
                return true;
            }
        }
        return false;
    }
}
