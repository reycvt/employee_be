<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\File;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:pdf,jpg,png']);

        $file = $request->file('file');
        $filename = time().'_'.$file->getClientOriginalName();
        $content = file_get_contents($file);

        // Generate random key and IV
        $key = random_bytes(32); // 256-bit key for AES-256
        $iv = random_bytes(16); // 128-bit IV for AES-CBC

        // Encrypt fil content
        $encrypted = openssl_encrypt($content, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        // Save encrypted file content
        $encryptedPath = 'encrypted/' . $filename;
        Storage::put($encryptedPath, $encrypted);

        // Save key and IV along with the encrypted data
        $fileModel = new File();
        $fileModel->filename = $filename;
        $fileModel->encrypted_path = $encryptedPath;
        $fileModel->key = base64_encode($key); // Save key
        $fileModel->iv = base64_encode($iv); // Save IV
        $fileModel->save();

        return response()->json(['message' => 'File uploaded and encrypted successfully'], 200);
    }

    public function decrypt(Request $request, $id)
    {
        $fileModel = File::findOrFail($id);
        $encryptedContent = Storage::get($fileModel->encrypted_path);

        // Retrieve key and IV from the database
        $key = base64_decode($fileModel->key);
        $iv = base64_decode($fileModel->iv);

        // Decrypt content
        $decrypted = openssl_decrypt($encryptedContent, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        $decryptedPath = 'decrypted/' . $fileModel->filename;
        Storage::put($decryptedPath, $decrypted);

        $fileModel->decrypted_path = $decryptedPath;
        $fileModel->save();

        // Remove encrypted path, key, and IV
        $fileModel->encrypted_path = 'decrypted';
        $fileModel->key = 'decrypted';
        $fileModel->iv = 'decrypted';
        $fileModel->decrypted_path = $decryptedPath;
        $fileModel->save();

        return response()->json(['message' => 'File decrypted successfully'], 200);
    }
    public function encryptedFiles()
    {
        $encryptedFiles = File::whereNotNull('encrypted_path')->get();
        return response()->json($encryptedFiles, 200);
    }

    public function decryptedFiles()
    {
        $decryptedFiles = File::whereNotNull('decrypted_path')->get();
        return response()->json($decryptedFiles, 200);
    }

    public function download($id, $type)
    {
        $fileModel = File::findOrFail($id);
        $path = ($type == 'encrypted') ? $fileModel->encrypted_path : $fileModel->decrypted_path;

        return response()->download(storage_path('app/' . $path));
    }

    public function delete($id)
    {
        $fileModel = File::findOrFail($id);
        Storage::delete($fileModel->encrypted_path);
        if ($fileModel->decrypted_path) {
            Storage::delete($fileModel->decrypted_path);
        }
        $fileModel->delete();

        return response()->json(['message' => 'File deleted successfully'], 200);
    }
}
