<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id')->nullable()->after('id');
            $table->unsignedBigInteger('comment_id')->nullable()->change();
            $table->index('post_id');
        });
    }

    public function down()
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex(['post_id']);
            $table->dropColumn('post_id');
        });
    }
};
