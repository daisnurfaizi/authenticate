<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->string('id', 50)->primary()->first();
            // change coloumss emmail to username
            $table->string('username', 50)->nullable()->after('email')->unique()->index();
            // add coloumns role_id to users table
            $table->string('role_id', 50)->nullable()->after('email');
            // photo 
            $table->string('photo')->nullable()->after('role_id');
            // last_login
            $table->timestamp('last_login')->nullable()->after('photo');
            //  access token
            $table->string('access_token')->nullable()->after('last_login');
            $table->datetime('access_token_expired_at')->nullable()->after('access_token');
            // refresh_token
            $table->string('refresh_token')->nullable()->after('access_token_expired_at');
            $table->datetime('refresh_token_expired_at')->nullable()->after('refresh_token');
            // status
            $table->integer('status')->default(1)->after('refresh_token_expired_at');
            // delete email column
            $table->dropColumn('email');
            // relation one to many to table app_role
            $table->foreign('role_id')
                ->references('id')
                ->on('app_role')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // drop coloumns role_id to users table
            $table->dropColumn('role_id');
            // drop coloumns photo to users table
            $table->dropColumn('photo');
            // drop coloumns last_login to users table
            $table->dropColumn('last_login');
            // drop coloumns refresh_token to users table
            $table->dropColumn('refresh_token');
            // drop coloumns status to users table
            $table->dropColumn('status');
            // drop foreign key role_id to users table
            $table->dropForeign(['role_id']);
        });
    }
};
