<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', [1, 2])->default(1)->after('password')->comment('1:active, 2:inactive');
            $table->enum('role_type', [1, 2])->default(1)->after('status')->comment('1:admin, 2:user');
            $table->string('mobile')->nullable()->after('role_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'role_type', 'mobile']);
        });
    }
};
