<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('document_type')->nullable()->after('notes');
            $table->string('document_number')->nullable()->after('document_type');
            $table->date('document_expiry')->nullable()->after('document_number');
            $table->date('date_of_birth')->nullable()->after('document_expiry');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('address')->nullable()->after('gender');
            $table->string('company_name')->nullable()->after('address');
            $table->string('alt_phone')->nullable()->after('company_name');
            $table->string('alt_email')->nullable()->after('alt_phone');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'document_type',
                'document_number',
                'document_expiry',
                'date_of_birth',
                'gender',
                'address',
                'company_name',
                'alt_phone',
                'alt_email',
            ]);
        });
    }
};
