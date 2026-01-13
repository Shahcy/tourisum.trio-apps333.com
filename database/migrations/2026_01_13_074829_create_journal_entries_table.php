<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            $table->string('entry_no', 50)->index();
            $table->date('date')->index();
            $table->text('description')->nullable();

            $table->enum('status', ['draft', 'posted'])->default('draft')->index();

            $table->timestamp('posted_at')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable()->index();

            $table->timestamps();

            $table->unique(['tenant_id', 'entry_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
