<?php

use App\Models\Community;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

function uploadImage(Request $request, $fieldName, $directory = 'images')
{
    if ($request->hasFile($fieldName)) {
        $image = $request->file($fieldName);
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path($directory), $imageName);
        return $directory . '/' . $imageName;
    }
    return null;
}

function uploadImages(Request $request, $fieldName, $directory = 'images')
{
    $uploadedImages = [];

    if ($request->hasFile($fieldName)) {
        $images = $request->file($fieldName);

        foreach ($images as $image) {
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path($directory), $imageName);
            $uploadedImages[] = $directory . '/' . $imageName;
        }
    }

    return implode(',', $uploadedImages);
}
function updateImages(Request $request, $fieldName = [], $directory = 'images', $id, $oldImages = [])
{
    $uploadedImages = [];
    if ($request->hasFile($fieldName)) {
        $images = $request->file($fieldName);

        foreach ($images as $image) {
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path($directory), $imageName);
            $uploadedImages[] = $directory . '/' . $imageName;
        }
    }
    if (!empty($oldImages)) {
        $community = Community::findOrFail($id);
        $currentImages = explode(',', $community->image);
        $imagesToKeep = array_intersect($currentImages, $oldImages);
        $imagesToDelete = array_diff($currentImages, $oldImages);
        foreach ($imagesToDelete as $imageToDelete) {
            if ($imageToDelete &&file_exists(public_path($imageToDelete)) ) {
                unlink(public_path($imageToDelete));
            }
        }
        $uploadedImages = array_merge($uploadedImages, $imagesToKeep);
    }else{
        $community = Community::findOrFail($id);
        $currentImages = explode(',', $community->image);
        foreach ($currentImages as $imageToDelete) {
            if (file_exists(public_path($imageToDelete))) {
                unlink(public_path($imageToDelete));
            }
        }
    }
    return implode(',', $uploadedImages);
}
function deleteImage($imagePath)
{
    if ($imagePath && file_exists(public_path($imagePath))) {
        unlink(public_path($imagePath));
    }
}
function deleteImages($imagePaths)
{
    $paths = explode(',', $imagePaths);
    foreach ($paths as $imagePath) {
        if ($imagePath && file_exists(public_path($imagePath))) {
            unlink(public_path($imagePath));
        }
    }
}

function paginate($query, $resourceClass, $limit = 10, $pageNumber = 1)
{
    // $paginatedData = $query->paginate($limit);
    $paginatedData = $query->paginate($limit, ['*'], 'page', $pageNumber);
    return $resourceClass::collection($paginatedData)->response()->getData(true);
}

