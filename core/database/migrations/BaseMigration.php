<?php

namespace Core\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class BaseMigration extends Migration
{
    protected function addCommonColumns(Blueprint $table): void
    {
        $table->unsignedTinyInteger('status')->default(0);
        $table->unsignedInteger('version')->default(1);
        $table->unsignedBigInteger('created_user_id')->nullable();
        $table->unsignedBigInteger('updated_user_id')->nullable();
        $table->timestamps();
        $table->softDeletes();
    }
}
