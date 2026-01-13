<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('nationality')->nullable();

            // Passport
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();

            // Visa
            $table->string('visa_type')->nullable();
            $table->date('visa_expiry')->nullable();

            // Status
            $table->string('status')->default('new'); // new, lead, interested, booked, lost

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'full_name']);
            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
