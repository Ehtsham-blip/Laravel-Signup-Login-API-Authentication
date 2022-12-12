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
        Schema::create('reset_passwords', function (Blueprint $table) {

            $table->string('email')->index();
            $table->string('code');
            $table->timestamp('created_at')->useCurrent();
            $table->enum('code_status',['Active','Inactive'])->default('Inactive');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reset_passwords');
    }
};