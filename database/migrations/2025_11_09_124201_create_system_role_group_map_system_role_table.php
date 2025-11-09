<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_role_group_map_system_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('system_role_group_id');
            $table->unsignedBigInteger('system_role_id');
            $table->unsignedInteger('status')->default(0);
            $table->unsignedBigInteger('created_user_id')->nullable();
            $table->unsignedBigInteger('updated_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_role_group_map_system_role');
    }
};
