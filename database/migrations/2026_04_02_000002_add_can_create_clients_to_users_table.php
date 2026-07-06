<?php

use App\Models\System\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCanCreateClientsToUsersTable extends Migration
{
    public function up()
    {
        $connection = (new User())->getConnectionName();
        $table = User::systemUsersTable();

        Schema::connection($connection)->table($table, function (Blueprint $blueprint) use ($connection, $table) {
            if (! Schema::connection($connection)->hasColumn($table, 'can_create_clients')) {
                $blueprint->boolean('can_create_clients')->default(false)->after('module_permissions');
            }
        });
    }

    public function down()
    {
        $connection = (new User())->getConnectionName();
        $table = User::systemUsersTable();

        Schema::connection($connection)->table($table, function (Blueprint $blueprint) use ($connection, $table) {
            if (Schema::connection($connection)->hasColumn($table, 'can_create_clients')) {
                $blueprint->dropColumn('can_create_clients');
            }
        });
    }
}
