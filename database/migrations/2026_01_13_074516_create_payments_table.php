<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            $table->enum('direction', ['in', 'out'])->index();
            $table->date('date')->index();

            $table->decimal('amount', 15, 2);
            $table->enum('method', ['cash', 'bank', 'card', 'wallet', 'other'])->default('cash')->index();

            // حساب الصندوق/البنك الذي تحركت منه/إليه الأموال
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();

            $table->foreignId('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();

            // ربط اختياري بأي كيان (TravelRequest / Payroll / Invoice ...)
            $table->string('reference_type')->nullable()->index();
            $table->unsignedBigInteger('reference_id')->nullable()->index();

            $table->string('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
