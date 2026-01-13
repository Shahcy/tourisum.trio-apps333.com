<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->date('date')->index();
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
