<?php

namespace Migrations;

use App\Database\Schema;

class password_reset extends Schema
{
    public function up()
    {
        $this->create('password_reset', function (Schema $table) {
            $table->string('email');
            $table->string('token');
            $table->string('expire_at');
            $table->unique('email');
            $table->unique('token');
        });
    }

    public function down()
    {
        $this->dropTable('password_reset');
    }
}
