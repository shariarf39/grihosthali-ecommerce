<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('is_admin')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('role_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        // Audit trail of every change made in the admin panel.
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('description')->nullable();
            $table->string('url')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event');
            $table->string('to');
            $table->string('subject');
            $table->string('status');
            $table->text('error')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('courier');
            $table->string('consignment_id')->nullable()->index();
            $table->string('tracking_code')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('cod_amount', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('mail_logs');
        Schema::dropIfExists('admin_logs');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['is_active', 'last_login_at']);
        });
        Schema::dropIfExists('roles');
    }
};
