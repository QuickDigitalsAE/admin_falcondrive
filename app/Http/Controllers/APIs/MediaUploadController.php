<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\ImageUrlTrait;

class MediaUploadController extends Controller
{
    use ImageUrlTrait;

    public function getAllFolders()
    {
        // Get all folders inside the 'public' disk
        $allFolders = Storage::disk('public')->directories();

        $folderList = [];

        foreach ($allFolders as $folder) {
            $folderKey = str_replace('/', '_', $folder);
            $folderName = ucwords(str_replace('_', ' ', $folder));
            $folderList[$folderKey] = $folderName;
        }

        if (empty($folderList)) {
            return response()->json([
                'status' => false,
                'message' => 'No images found.',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => true,
            'message' => 'Folder list fetched successfully.',
            'data' => $folderList
        ], Response::HTTP_OK);
    }
    
    public function getAllFiles(Request $request)
    {
        $folderKey = $request->query('folder_key');
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 99999999); // default 10
        $page = (int) $request->query('page', 1); // default 1

        if (!$folderKey) {
            return response()->json([
                'status' => false,
                'message' => 'folder_key parameter is required.',
                'data' => []
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!Storage::disk('public')->exists($folderKey)) {
            return response()->json([
                'status' => false,
                'message' => "Folder '{$folderKey}' does not exist.",
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        // Get all files inside the folder
        $allFiles = Storage::disk('public')->allFiles($folderKey);
        $fileList = [];

        foreach ($allFiles as $filePath) {
            $fileName = pathinfo($filePath, PATHINFO_BASENAME);

            // Search filter: case-insensitive %search%
            if ($search && !Str::contains(strtolower($fileName), strtolower($search))) {
                continue;
            }

            $fileList[] = [
                'file_path' => $filePath,
                'file_full_path' => $this->getImageUrl($filePath), // Can be renamed getFileUrl() if you prefer
                'file_name' => $fileName,
                'file_type' => pathinfo($filePath, PATHINFO_EXTENSION)
            ];
        }

        if($fileList){
            // Paginate results manually
            $total = count($fileList);
            $offset = ($page - 1) * $perPage;
            $pagedData = array_slice($fileList, $offset, $perPage);

            return response()->json([
                'status' => true,
                'message' => 'Files fetched successfully.',
                'data' => [
                    'list' => $pagedData,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => ceil($total / $perPage)
                    ]
                ]
            ], Response::HTTP_OK);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'File not found!',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /* Image insertion part POST */
    public function uploadImage(Request $request)
    {
        // Define validation rules
        $validator = [
            'folder_name' => 'required|string',
            'file' => 'required|file'
        ];

        $request->validate($validator);

        $folder_name = $request->folder_name;
        $imagePath = $image_fullPath = "";

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newFileName = $originalName . '_' . time() . '.' . $extension;

            $path = $file->storeAs($folder_name, $newFileName, 'public');
            $file_fullPath = $this->getImageUrl($path);

            return response()->json([
                'status' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'folder_name' => $folder_name,
                    'file_path' => $path,
                    'file_full_path' => $file_fullPath,
                    'file_type' => $extension
                ]
            ], Response::HTTP_CREATED);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'File not found!',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function deleteFile(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);

        $filePath = $request->input('file_path');

        // Check if the file exists
        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json([
                'status' => false,
                'message' => 'File not found.',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        // Delete the file
        Storage::disk('public')->delete($filePath);

        return response()->json([
            'status' => true,
            'message' => 'File deleted successfully.',
            'data' => []
        ], Response::HTTP_OK);
    }

}
