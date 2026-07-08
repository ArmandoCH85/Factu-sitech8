<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// ponytail: anonymous class to match repo convention (avoids class-name vs filename collision in Laravel 10+)
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defensive addColumns for the KISS CRM (crm-comercial-kiss). Each column is
     * wrapped in its own Schema::hasColumn guard so re-runs are no-ops.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sale_opportunities')) {
            Schema::table('sale_opportunities', function (Blueprint $table) {
                if (!Schema::hasColumn('sale_opportunities', 'crm_stage_id')) {
                    $table->unsignedInteger('crm_stage_id')->nullable()->after('observation');
                    $table->foreign('crm_stage_id')->references('id')->on('crm_pipeline_stages');
                }

                if (!Schema::hasColumn('sale_opportunities', 'crm_source')) {
                    $table->string('crm_source', 50)->nullable()->after('crm_stage_id');
                }

                if (!Schema::hasColumn('sale_opportunities', 'expected_close_date')) {
                    $table->date('expected_close_date')->nullable()->after('crm_source');
                }

                if (!Schema::hasColumn('sale_opportunities', 'won_at')) {
                    $table->timestamp('won_at')->nullable()->after('expected_close_date');
                }

                if (!Schema::hasColumn('sale_opportunities', 'lost_at')) {
                    $table->timestamp('lost_at')->nullable()->after('won_at');
                }

                if (!Schema::hasColumn('sale_opportunities', 'last_activity_at')) {
                    $table->timestamp('last_activity_at')->nullable()->after('lost_at');
                }

                if (!Schema::hasColumn('sale_opportunities', 'next_activity_at')) {
                    $table->timestamp('next_activity_at')->nullable()->after('last_activity_at');
                    $table->index('next_activity_at', 'sale_opps_next_activity_idx');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('sale_opportunities')) {
            Schema::table('sale_opportunities', function (Blueprint $table) {
                if (Schema::hasColumn('sale_opportunities', 'crm_stage_id')) {
                    $table->dropForeign(['crm_stage_id']);
                    $table->dropColumn('crm_stage_id');
                }
                if (Schema::hasColumn('sale_opportunities', 'crm_source')) {
                    $table->dropColumn('crm_source');
                }
                if (Schema::hasColumn('sale_opportunities', 'expected_close_date')) {
                    $table->dropColumn('expected_close_date');
                }
                if (Schema::hasColumn('sale_opportunities', 'won_at')) {
                    $table->dropColumn('won_at');
                }
                if (Schema::hasColumn('sale_opportunities', 'lost_at')) {
                    $table->dropColumn('lost_at');
                }
                if (Schema::hasColumn('sale_opportunities', 'last_activity_at')) {
                    $table->dropColumn('last_activity_at');
                }
                if (Schema::hasColumn('sale_opportunities', 'next_activity_at')) {
                    $table->dropColumn('next_activity_at');
                }
            });
        }
    }
};
