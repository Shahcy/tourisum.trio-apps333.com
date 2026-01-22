<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name');
            $table->string('code');
            $table->string('type')->default('gds');
            $table->string('mode')->default('manual');
            $table->string('status')->default('pending');
            $table->json('config')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'code']);
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        $defaults = [
            ['name' => 'Manual', 'code' => 'manual'],
            ['name' => 'Amadeus', 'code' => 'amadeus'],
            ['name' => 'Sabre', 'code' => 'sabre'],
            ['name' => 'Travelport (Galileo)', 'code' => 'travelport'],
        ];

        $tenantIds = DB::table('tenants')->pluck('id');
        foreach ($tenantIds as $tenantId) {
            foreach ($defaults as $provider) {
                DB::table('providers')->insert([
                    'tenant_id' => $tenantId,
                    'name' => $provider['name'],
                    'code' => $provider['code'],
                    'type' => 'gds',
                    'mode' => 'manual',
                    'status' => 'pending',
                    'config' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
