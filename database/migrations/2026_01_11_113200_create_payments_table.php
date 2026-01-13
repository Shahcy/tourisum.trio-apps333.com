<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();

            $table->date('paid_at')->default(now());
            $table->decimal('amount', 12, 2);

            $table->string('method')->default('cash'); // cash, card, bank, wallet
            $table->string('reference')->nullable(); // رقم إيصال/تحويل

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'paid_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
