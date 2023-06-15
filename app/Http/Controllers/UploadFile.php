<?php

namespace App\Http\Controllers;

use App\Models\decrypted;
use App\Models\File;
use Illuminate\Http\Request;
// use App\Models\Document;
use App\Models\Encrypted;

class UploadFile extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
                    'path' => 'required|mimes:docx,pdf',
                ]);
        if ($request->hasFile('path')) {
            $path = time() . '.' . $request->path->extension();
            $request->file('path')->storeAs('public/encrypt/', $path);
            Encrypted::create([
                'name' => $request->name,
                'path' => encrypt($path)
            ]);
        } else {
            Encrypted::create([
                'name' => $request->name
            ]);
        }
        return response()->json([
            'message' => 'file terunggah',
        ], 200);
    }
    // public function upload(Request $request)
    // {
    //     // Memvalidasi dan menyimpan file yang diunggah
    //     $request->validate([
    //         'file' => 'required|mimes:docx,pdf',
    //     ]);

    //     $file = $request->file('file');
    //     $filename = $file->getClientOriginalName();
    //     $extension = $file->getClientOriginalExtension();
    //     $path = $file->storeAs('encrypted', $filename);

    //     // Mengenkripsi file dengan AES
    //     $encryptedPath = $this->encryptFile($path, $extension);

    //     return response()->json(['encrypted_path' => $encryptedPath]);
    // }

    private function encryptFile($path, $extension)
    {
        $encryptionKey = 'test';
        $encryptedPath = 'encrypted/' . basename($path) . '.enc';

        $fileContent = encrypted::get($path);
        $encryptedContent = openssl_encrypt($fileContent, 'AES-256-CBC', $encryptionKey, 0, 'test');

        encrypted::put($encryptedPath, $encryptedContent);

        // Menghapus file asli yang tidak terenkripsi
        File::delete($path);

        return $encryptedPath;
    }
    public function decrypt(Request $request)
{
    // Mendapatkan jalur terenkripsi dan ekstensi dari permintaan
    $encryptedPath = $request->input('encrypted_path');
    $extension = $request->input('extension');

    // Mendapatkan jalur asli file yang belum terenkripsi
    $decryptedPath = $this->decryptFile($encryptedPath, $extension);

    return response()->json(['decrypted_path' => $decryptedPath]);
}
    private function decryptFile($encryptedPath, $extension)
{
    $encryptionKey = 'test';
    $decryptedPath = 'decrypted/' . basename($encryptedPath, '.enc') . '.' . $extension;

    $encryptedContent = encrypted::get($encryptedPath);
    $decryptedContent = openssl_decrypt($encryptedContent, 'AES-256-CBC', $encryptionKey, 0, 'test');

    decrypted::put($decryptedPath, $decryptedContent);

    return $decryptedPath;
}
}
