<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToPaymentOrders extends Migration
{
    public function up()
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_orders', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('client_id');
            }
            if (! Schema::hasColumn('payment_orders', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down()
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            if (Schema::hasColumn('payment_orders', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('payment_orders', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
}