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
        Schema::create('app_permissions', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('name', 50)->unique();
            $table->timestamps();
            // relation one to many to table app_role_has_permissions
            $table->foreignId('permission_id')->constrained('app_role_has_permissions')->onDelete('null');
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
