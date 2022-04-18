<?php

namespace Migrations;

use App\Database\Schema;

class email_verification extends Schema
{
    public function up()
    {
        $this->create('email_verification', function (Schema $table) {
            $table->string('email');
            $table->string('token');
            $table->string('expire_at');
            $table->unique('email');
            $table->unique('token');
        });
    }

    public function down()
    {
        $this->dropTable('email_verification');
    }
}
