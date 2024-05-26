<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('files', function (Blueprint $table) {
        $table->id();
        $table->string('filename');
        $table->string('encrypted_path');
        $table->string('decrypted_path')->nullable();
        $table->string('iv');
        $table->timestamps();
        $table->softDeletes();
    });
}

public function down()
{
    Schema::dropIfExists('files');
}
};
