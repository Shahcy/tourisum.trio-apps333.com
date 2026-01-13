<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();

            $table->string('type'); // flight, hotel, tour, transport, group
            $table->string('reference')->nullable(); // مرجع داخلي/خارجي
            $table->string('destination')->nullable(); // وجهة عامة (اختياري)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);

            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->string('status')->default('draft'); // draft, confirmed, cancelled, completed

            // ملفات (Voucher / Invoice PDF / إلخ)
            $table->string('voucher_path')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
