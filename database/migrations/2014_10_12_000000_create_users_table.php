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
        if (!Schema::hasTable('users')) {
            Schema::create('users',function (Blueprint $table){
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->smallInteger('contact_no');
                $table->string('picture');
                $table->enum('verified_status',['Verified','Un-verified'])->nullable()->default('Un-verified');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

                $table->rememberToken();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};