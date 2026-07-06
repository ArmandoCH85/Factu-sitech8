<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyCreatedByUserIdToClients extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('clients', 'created_by_user_id')) {
            return;
        }

        try {
            Schema::table('clients', function (Blueprint $table) {
                $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // La FK ya existe o hay datos huérfanos. Para no bloquear el deploy,
            // se deja registrada la advertencia en el log.
            \Illuminate\Support\Facades\Log::warning('No se pudo crear FK clients.created_by_user_id: '.$e->getMessage());
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
    }
}