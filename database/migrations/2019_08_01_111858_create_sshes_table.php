<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSshesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sshes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('host');
            $table->string('user')->nullable();
            $table->string('hostname');
            $table->string('password')->nullable();
            $table->smallInteger('port')->nullable();
            $table->string('identity_file')->nullable();
            $table->string('strict_host_key_checking')->nullable();
            $table->string('user_known_host_file')->nullable();
            $table->enum('log_level', [
                'QUIET',
                'FATAL',
                'ERROR',
                'INFO',
                'VERBOSE',
                'DEBUG',
                'DEBUG1',
                'DEBUG2',
                'DEBUG3'
            ])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sshes');
    }
}
