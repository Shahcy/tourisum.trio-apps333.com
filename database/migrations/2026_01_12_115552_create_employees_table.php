<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            $table->string('employee_code')->nullable()->index(); // رقم وظيفي داخلي
            $table->string('full_name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();

            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_title_id')->nullable()->constrained('job_titles')->nullOnDelete();

            $table->date('hire_date')->nullable();
            $table->decimal('basic_salary', 12, 2)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['tenant_id', 'employee_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
