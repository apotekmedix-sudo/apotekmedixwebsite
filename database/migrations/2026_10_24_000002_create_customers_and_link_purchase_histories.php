<?php

use App\Models\Customer;
use App\Models\PurchaseHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('identity_key', 64)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('buyer_type', 20)->default('umum');
            $table->string('name');
            $table->string('requester_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable()->index();
            $table->string('outlet_name')->nullable();
            $table->string('pbf_name')->nullable();
            $table->text('address')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kota')->nullable();
            $table->string('sia')->nullable();
            $table->string('sipa')->nullable();
            $table->string('no_izin_pbf')->nullable();
            $table->string('apj')->nullable();
            $table->string('sika')->nullable();
            $table->timestamp('last_order_at')->nullable();
            $table->timestamps();
        });

        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
            $table->index(['customer_id', 'created_at']);
        });

        PurchaseHistory::query()->orderBy('id')->chunkById(250, function ($orders): void {
            foreach ($orders as $order) {
                $customer = Customer::syncFromOrder($order);
                DB::table('purchase_histories')
                    ->where('id', $order->id)
                    ->update(['customer_id' => $customer->id]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'created_at']);
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::dropIfExists('customers');
    }
};
