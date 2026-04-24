<?php

use Illuminate\Database\Migrations\Migration;

class TenantAddTuQuipuSkin extends Migration
{
    public function up()
    {
        DB::table('skins')->insert([
            ['name' => 'Tu Quipu', 'filename' => 'tu-quipu.css'],
        ]);
    }

    public function down()
    {
        DB::table('skins')->where('name', 'Tu Quipu')->where('filename', 'tu-quipu.css')->delete();
    }
}
