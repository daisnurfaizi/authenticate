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
        if (!Schema::hasTable('app_role') || !Schema::hasTable('app_permissions')) {
            return;
        }
        Schema::create('app_role_has_permissions', function (Blueprint $table) {
            $table->string('role_id', 50);
            $table->string('permission_id', 50);
            $table->primary(['role_id', 'permission_id']);
            $table->timestamps();
            // relation namy to one to table app_role
            $table->foreign('role_id')->references('id')->on('app_role')->onDelete('cascade');
            // relation many to one to table app_permissions
            $table->foreign('permission_id')->references('id')->on('app_permissions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_role_has_permissions');
    }
};
