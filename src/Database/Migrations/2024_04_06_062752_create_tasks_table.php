<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('title');
            $table->string('level')->nullable();
            $table->string('status')->nullable();
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('lock')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}

