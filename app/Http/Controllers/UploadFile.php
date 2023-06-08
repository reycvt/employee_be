<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadFile extends Controller
{
    public function uploadFile(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
             // Pastikan file yang diunggah adalah PDF atau DOCX
             if ($extension == 'pdf' || $extension == 'docx') {
                $path = $file->storeAs('public', $filename);
    
                return response()->json([
                    'message' => 'File berhasil diunggah.',
                    'path' => $path,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'File harus berupa PDF atau DOCX.',
                ], 400);
            }
        }
    
        return response()->json([
            'message' => 'Tidak ada file yang diunggah.',
        ], 400);
    }
}
