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
        Schema::create('app_role', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('name', 50)->unique();
            $table->text('description')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            // relation one to many to table users
            $table->foreignId('role_id')->constrained('users')->onDelete('null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_role');
    }
};
