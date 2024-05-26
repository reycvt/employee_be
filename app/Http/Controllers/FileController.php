<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Crypt\AES;
use App\Models\File;

class FileController extends Controller
{
        public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:pdf,jpg,png']);

        $file = $request->file('file');
        $filename = time().'_'.$file->getClientOriginalName();
        $content = file_get_contents($file);

        // Generate IV
        $iv = random_bytes(16); // Untuk AES, IV harus memiliki panjang 16 byte

        $cipher = new AES('cbc');
        $key = env('AES_KEY'); // Ambil kunci dari file .env
        $cipher->setKey($key);

        // Set IV
        $cipher->setIV($iv);

        // Encrypt
        $encrypted = $cipher->encrypt($content);

        $encryptedPath = 'encrypted/' . $filename;
        Storage::put($encryptedPath, $encrypted);

        // Simpan IV bersama dengan data terenkripsi
        $fileModel = new File();
        $fileModel->filename = $filename;
        $fileModel->encrypted_path = $encryptedPath;
        $fileModel->iv =  base64_encode($iv); // Simpan IV
        $fileModel->save();

        return response()->json(['message' => 'File uploaded and encrypted successfully'], 200);
    }

    public function decrypt(Request $request, $id)
    {
        $fileModel = File::findOrFail($id);
        $encryptedContent = Storage::get($fileModel->encrypted_path);

        $cipher = new AES('cbc');
        $key = env('AES_KEY'); // Ambil kunci dari file .env
        $cipher->setKey($key);

        // Ambil IV dari data terenkripsi
        $iv = base64_decode($fileModel->iv);

        // Set IV
        $cipher->setIV($iv);

        // Decrypt
        $decrypted = $cipher->decrypt($encryptedContent);

        $decryptedPath = 'decrypted/' . $fileModel->filename;
        Storage::put($decryptedPath, $decrypted);

        $fileModel->decrypted_path = $decryptedPath;
        $fileModel->save();

        // Hapus path terenkripsi dan IV
        $fileModel->encrypted_path = 'telah terdekripsi';
        $fileModel->iv = 'telah terdekripsi';
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
