<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->nullable()->after('discount_price');
            $table->unsignedInteger('low_stock_alert')->default(5)->after('stock');
        });

        // CRM: one row per customer, keyed by normalized phone (covers guest checkouts too).
        Schema::create('crm_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone', 20)->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('source')->default('website');
            $table->json('tags')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_order_at')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->text('message');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['type', 'completed_at', 'due_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('crm_customer_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->timestamp('delivered_at')->nullable()->after('admin_note');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->integer('quantity');
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->string('reference')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'created_at']);
        });

        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crm_customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('reason');
            $table->string('resolution')->default('refund');
            $table->text('customer_note')->nullable();
            $table->string('status')->default('requested');
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->string('refund_method')->nullable();
            $table->string('refund_reference')->nullable();
            $table->text('admin_note')->nullable();
            $table->boolean('restocked')->default(false);
            $table->string('created_by')->default('customer');
            $table->timestamp('received_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('condition')->default('resellable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_return_items');
        Schema::dropIfExists('order_returns');
        Schema::dropIfExists('stock_movements');
        Schema::table('orders', function (Blueprint $t) {
            $t->dropConstrainedForeignId('crm_customer_id');
            $t->dropColumn('delivered_at');
        });
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_customers');
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['cost_price', 'low_stock_alert']));
    }
};
