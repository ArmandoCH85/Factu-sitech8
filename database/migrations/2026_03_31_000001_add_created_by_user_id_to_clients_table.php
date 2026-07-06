<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByUserIdToClientsTable extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'created_by_user_id')) {
                $table->unsignedInteger('created_by_user_id')->nullable()->after('id');
                $table->index('created_by_user_id');
            }
        });

        if (!Schema::hasColumn('clients', 'created_by_user_id')) {
            return;
        }

        try {
            Schema::table('clients', function (Blueprint $table) {
                $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // La FK ya existe o hay datos huérfanos; se ignora para no bloquear el deploy.
        }
    }

    public function down()
    {
        try {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropForeign('clients_created_by_user_id_foreign');
            });
        } catch (\Throwable $e) {
        }

        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'created_by_user_id')) {
                $table->dropIndex(['created_by_user_id']);
                $table->dropColumn('created_by_user_id');
            }
        });
    }
}

