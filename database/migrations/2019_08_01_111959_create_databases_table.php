<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatabasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('databases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('driver', ['sqlite', 'mysql', 'pgsql', 'sqlsrv'])->default('mysql');
            $table->string('url')->nullable();
            $table->string('host')->default('127.0.0.1');
            $table->unsignedSmallInteger('port')->default(3306);
            $table->string('name')->default('default')->unique();
            $table->string('database')->default('');
            $table->string('username')->default('homestead');
            $table->string('password')->default('secret');
            $table->string('unix_socket')->default('');
            $table->string('charset')->default('utf8mb4');
            $table->string('collation')->default('utf8mb4_unicode_ci');
            $table->string('prefix')->default('');
            $table->boolean('prefix_indexes')->default(true);
            $table->boolean('strict')->default(true);
            $table->string('engine')->nullable()->default(null);
            $table->json('options')->nullable()->default(null);
            $table->bigInteger('ssh_id')->unsigned()->index()->nullable()->default(null);
            $table->foreign('ssh_id')->references('id')->on('sshes')->onDelete('restrict');
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
        Schema::dropIfExists('databases');
    }
}
