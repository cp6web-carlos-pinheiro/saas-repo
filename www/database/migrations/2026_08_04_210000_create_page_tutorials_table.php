<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_tutorials')) {
            return;
        }

        Schema::create('page_tutorials', function (Blueprint $table): void {
            $table->id();
            $table->string('route_name', 190)->unique();
            $table->string('title', 190)->nullable();
            $table->longText('content_html');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_tutorials');
    }
};
