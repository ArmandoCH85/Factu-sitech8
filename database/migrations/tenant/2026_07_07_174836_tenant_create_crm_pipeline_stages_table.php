<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// ponytail: anonymous class to match repo convention (avoids class-name vs filename collision in Laravel 10+)
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defensive migration: catalog of CRM pipeline stages for KISS CRM (crm-comercial-kiss).
     * 7 stages seeded inline: new, contacted, interested (type=lead),
     * proposal, negotiation (type=deal), won, lost (type=terminal).
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('crm_pipeline_stages')) {
            Schema::create('crm_pipeline_stages', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 50)->unique();
                $table->string('name', 100);
                $table->enum('type', ['lead', 'deal', 'terminal']);
                $table->integer('position');
                $table->boolean('is_won')->default(false);
                $table->boolean('is_lost')->default(false);
                $table->string('color', 20)->nullable();
                $table->timestamps();
            });

            // Inline seed — only runs when table is freshly created.
            $stages = [
                ['code' => 'new',          'name' => 'Nuevo',       'type' => 'lead',     'position' => 1, 'is_won' => false, 'is_lost' => false, 'color' => '#6c757d'],
                ['code' => 'contacted',    'name' => 'Contactado',  'type' => 'lead',     'position' => 2, 'is_won' => false, 'is_lost' => false, 'color' => '#0dcaf0'],
                ['code' => 'interested',   'name' => 'Interesado',  'type' => 'lead',     'position' => 3, 'is_won' => false, 'is_lost' => false, 'color' => '#0d6efd'],
                ['code' => 'proposal',     'name' => 'Propuesta',   'type' => 'deal',     'position' => 4, 'is_won' => false, 'is_lost' => false, 'color' => '#6610f2'],
                ['code' => 'negotiation',  'name' => 'Negociacion', 'type' => 'deal',     'position' => 5, 'is_won' => false, 'is_lost' => false, 'color' => '#fd7e14'],
                ['code' => 'won',          'name' => 'Ganado',      'type' => 'terminal', 'position' => 6, 'is_won' => true,  'is_lost' => false, 'color' => '#198754'],
                ['code' => 'lost',         'name' => 'Perdido',     'type' => 'terminal', 'position' => 7, 'is_won' => false, 'is_lost' => true,  'color' => '#dc3545'],
            ];

            $now = now();
            foreach ($stages as $stage) {
                if (DB::table('crm_pipeline_stages')->where('code', $stage['code'])->doesntExist()) {
                    DB::table('crm_pipeline_stages')->insert(array_merge($stage, [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_pipeline_stages');
    }
};
