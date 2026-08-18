<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 190)->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true)->index();
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')->where('is_platform_admin', true)->orderBy('id')->each(function (object $user): void {
            DB::table('admins')->insertOrIgnore(['name' => $user->name, 'email' => $user->email, 'password' => $user->password, 'is_active' => $user->is_active, 'created_at' => now(), 'updated_at' => now()]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['is_platform_admin']);
            $table->dropColumn('is_platform_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_platform_admin')->default(false)->after('is_active');
            $table->index('is_platform_admin');
        });

        Schema::dropIfExists('admins');
    }
};
