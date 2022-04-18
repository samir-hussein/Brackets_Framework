<?php

namespace Migrations;

use App\Database\Schema;

class users extends Schema
{
    public function up()
    {
        $this->create('users', function (Schema $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamp('email_verifed_at')->nullable();
            $table->unique('email');
        });
    }

    public function down()
    {
        $this->dropTable('users');
    }
}
