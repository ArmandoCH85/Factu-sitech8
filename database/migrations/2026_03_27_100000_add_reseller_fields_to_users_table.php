<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResellerFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'reseller_id')) {
                $table->unsignedInteger('reseller_id')->nullable()->after('id');
                $table->foreign('reseller_id')->references('id')->on('users')->onDelete('set null');
            }

            if (!Schema::hasColumn('users', 'status')) {
                $table->boolean('status')->default(true)->after('api_token');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'reseller_id')) {
                $table->dropForeign(['reseller_id']);
                $table->dropColumn('reseller_id');
            }

            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}
