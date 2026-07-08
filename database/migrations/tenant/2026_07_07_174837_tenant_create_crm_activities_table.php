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
     * Defensive migration: central activity log (notes/calls/tasks/status_change/quotation)
     * for the KISS CRM (crm-comercial-kiss).
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('crm_activities')) {
            Schema::create('crm_activities', function (Blueprint $table) {
                $table->increments('id');
                $table->enum('type', ['note', 'call', 'task', 'status_change', 'quotation']);
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('sale_opportunity_id');
                $table->text('description')->nullable();
                $table->json('payload')->nullable();
                $table->enum('status', ['pending', 'done'])->default('pending');
                $table->timestamp('due_date')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedInteger('completed_by_user_id')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('sale_opportunity_id')->references('id')->on('sale_opportunities');

                // Performance indexes for the spec KPIs.
                $table->index(['sale_opportunity_id', 'type'], 'crm_activities_opp_type_idx');
                $table->index(['user_id', 'type', 'status', 'due_date'], 'crm_activities_kpi_idx');
                $table->index(['status', 'due_date'], 'crm_activities_overdue_idx');
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
        Schema::dropIfExists('crm_activities');
    }
};
