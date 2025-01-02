<?php
namespace App\Traits;

use Illuminate\Http\Request;
use File;

trait FileUploadTrait
{
    /**
     * Upload an image, delete old file if specified.
     *
     * @param Request $request
     * @param string $inputName
     * @param string|null $oldPath
     * @param string $path
     * @return string|null
     */
    public function uploadImage(Request $request, $inputName, $oldPath = null, $path = "/uploads")
    {
        if ($request->hasFile($inputName)) {
            $image = $request->file($inputName);  // Access uploaded file
            $ext = $image->getClientOriginalExtension();  // Get file extension
            $imageName = 'media_' . uniqid() . '.' . $ext;  // Generate unique filename

            // Move file to the target directory
            $image->move(public_path($path), $imageName);

            // If an old file exists, delete it
            if ($oldPath && File::exists(public_path($oldPath))) {
                File::delete(public_path($oldPath));
            }

            return $path . '/' . $imageName;  // Return the new file path
        }

        return null;
    }

    /**
     * Remove an image from storage.
     *
     * @param string $path
     * @return void
     */
    public function removeImage(string $path): void
    {
        if (File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}
