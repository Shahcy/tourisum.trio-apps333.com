<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_number')->nullable()->after('id');
            $table->string('booking_type')->nullable()->after('type');
            $table->string('channel')->nullable()->after('booking_type');
            $table->unsignedBigInteger('agent_id')->nullable()->after('channel');
            $table->string('branch')->nullable()->after('agent_id');
            $table->string('priority')->nullable()->after('branch');
            $table->json('tags')->nullable()->after('priority');
            $table->string('source_campaign')->nullable()->after('tags');

            $table->decimal('subtotal', 12, 2)->default(0)->after('total_amount');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('discount_percent', 6, 2)->default(0)->after('discount_amount');
            $table->decimal('taxes', 12, 2)->default(0)->after('discount_percent');
            $table->decimal('service_fees', 12, 2)->default(0)->after('taxes');
            $table->decimal('grand_total', 12, 2)->default(0)->after('service_fees');
            $table->decimal('cost_total', 12, 2)->default(0)->after('grand_total');
            $table->decimal('profit', 12, 2)->default(0)->after('cost_total');
            $table->decimal('profit_margin', 6, 2)->default(0)->after('profit');
            $table->string('currency', 10)->nullable()->after('profit_margin');
            $table->decimal('exchange_rate', 12, 4)->default(1)->after('currency');
            $table->boolean('price_locked')->default(false)->after('exchange_rate');
            $table->string('payment_status')->default('unpaid')->after('price_locked');

            $table->text('cancellation_policy')->nullable()->after('payment_status');
            $table->text('refund_rules')->nullable()->after('cancellation_policy');
            $table->decimal('cancellation_fee', 12, 2)->default(0)->after('refund_rules');
            $table->decimal('refund_amount', 12, 2)->default(0)->after('cancellation_fee');

            $table->index(['tenant_id', 'booking_number']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'booking_number']);

            $table->dropColumn([
                'booking_number',
                'booking_type',
                'channel',
                'agent_id',
                'branch',
                'priority',
                'tags',
                'source_campaign',
                'subtotal',
                'discount_amount',
                'discount_percent',
                'taxes',
                'service_fees',
                'grand_total',
                'cost_total',
                'profit',
                'profit_margin',
                'currency',
                'exchange_rate',
                'price_locked',
                'payment_status',
                'cancellation_policy',
                'refund_rules',
                'cancellation_fee',
                'refund_amount',
            ]);
        });
    }
};
