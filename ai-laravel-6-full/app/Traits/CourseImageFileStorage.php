<?php

namespace App\Traits;

use App\Models\Course;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait CourseImageFileStorage
{
    public function storeCourseImage(?UploadedFile $uploadedFile, Course $course): ?string
    {
        if ($uploadedFile) {
            $path = basename(Storage::disk('public')->putFileAs('courses', $uploadedFile, $course->abbreviation . '.png'));
            return $path;
        }
        return null;
    }

    public function deleteCourseImage(Course $course): bool
    {
        if (Storage::disk('public')->exists('courses/' . $course->abbreviation . '.png')) {
            Storage::disk('public')->delete('courses/' . $course->abbreviation . '.png');
            return true;
        }
        return false;
    }

    public function deleteImageFile(?string $abbreviation): bool
    {
        if ($abbreviation !== null) {
            if (Storage::disk('public')->exists('courses/' . $abbreviation . '.png')) {
                Storage::disk('public')->delete('courses/' . $abbreviation . '.png');
                return true;
            }
        }
        return false;
    }
}
