<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traveler_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_traveler_id')->constrained('booking_travelers')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('file_path');
            $table->string('visibility')->default('internal');
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traveler_documents');
    }
};