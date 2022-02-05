<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->string("slug")->unique();
            $table->integer("timeout")->default(30);
            $table->integer("exitcode")->nullable();
            $table->json("data");
            $table->boolean("closed")->default(false);
            $table->boolean("success")->nullable();
            $table->dateTime("started_at");
            $table->bigInteger("started_at_unix");
            $table->text("remark")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('processes');
    }
}
