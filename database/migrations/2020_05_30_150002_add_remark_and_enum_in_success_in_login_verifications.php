<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddRemarkAndEnumInSuccessInLoginVerifications extends Migration
{
    public function __construct()
    {
        
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table('login_verifications', function (Blueprint $table) {
            $table->enum("progress", ['success', 'pending', 'failed'])->default("pending");
            $table->text("remark")->after("payload");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table('login_verifications', function (Blueprint $table) {
            $table->dropColumn("remark", "progress");
        });
    }
}
