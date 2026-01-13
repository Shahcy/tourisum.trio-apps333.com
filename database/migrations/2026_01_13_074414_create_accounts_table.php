<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            $table->string('code', 50)->index();
            $table->string('name');

            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense'])->index();

            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
