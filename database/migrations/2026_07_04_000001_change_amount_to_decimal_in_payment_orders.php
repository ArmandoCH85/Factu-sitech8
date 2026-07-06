<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAmountToDecimalInPaymentOrders extends Migration
{
    public function up()
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            if (Schema::hasColumn('payment_orders', 'amount')) {
                $table->decimal('amount', 12, 2)->change();
            }
        });
    }

    public function down()
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            if (Schema::hasColumn('payment_orders', 'amount')) {
                $table->float('amount')->change();
            }
        });
    }
}