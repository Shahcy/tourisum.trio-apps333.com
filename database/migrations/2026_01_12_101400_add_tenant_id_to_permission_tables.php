<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'tenant_id')) {
                $table->dropColumn('tenant_id');
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'tenant_id')) {
                $table->dropColumn('tenant_id');
            }
        });
    }
};
