<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // لو كان عندك tenants بدون domain سابقاً
            if (! Schema::hasColumn('tenants', 'domain')) {
                $table->string('domain')->nullable()->after('name');
            }

            if (! Schema::hasColumn('tenants', 'secondary_color')) {
                $table->string('secondary_color', 20)->nullable()->after('primary_color');
            }

            if (! Schema::hasColumn('tenants', 'accent_color')) {
                $table->string('accent_color', 20)->nullable()->after('secondary_color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'accent_color')) {
                $table->dropColumn('accent_color');
            }

            if (Schema::hasColumn('tenants', 'secondary_color')) {
                $table->dropColumn('secondary_color');
            }

            if (Schema::hasColumn('tenants', 'domain')) {
                $table->dropColumn('domain');
            }
        });
    }
};
