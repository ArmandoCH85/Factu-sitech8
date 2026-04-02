<?php

use App\Models\System\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMasterToSystemUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_master')) {
                $table->boolean('is_master')->default(false)->after('can_create_clients');
            }
        });

        $resellerIds = User::whereNotNull('reseller_id')->distinct()->pluck('reseller_id');
        foreach ($resellerIds as $rid) {
            $firstId = User::where('reseller_id', $rid)->orderBy('id')->value('id');
            if ($firstId !== null) {
                User::where('id', $firstId)->update(['is_master' => true]);
            }
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_master')) {
                $table->dropColumn('is_master');
            }
        });
    }
}
