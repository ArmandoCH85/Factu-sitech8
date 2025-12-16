<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Limpiar valores por defecto 'Cliente' o 'cliente'
        DB::table('restaurant_tables')
            ->whereIn('cliente', ['Cliente', 'cliente', ''])
            ->update(['cliente' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Opcional: restaurar valor por defecto
        DB::table('restaurant_tables')
            ->whereNull('cliente')
            ->update(['cliente' => 'Cliente']);
    }
};
