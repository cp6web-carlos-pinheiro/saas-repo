<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            if (! Schema::hasColumn('suppliers', 'person_type')) {
                $table->string('person_type', 2)->default('PJ')->after('name');
            }

            if (! Schema::hasColumn('suppliers', 'tax_id')) {
                $table->string('tax_id', 30)->nullable()->after('person_type');
            }
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 180);
            $table->string('person_type', 2)->default('PJ');
            $table->string('tax_id', 30)->nullable();
            $table->string('email', 180)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'person_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');

        Schema::table('suppliers', function (Blueprint $table): void {
            if (Schema::hasColumn('suppliers', 'tax_id')) {
                $table->dropColumn('tax_id');
            }

            if (Schema::hasColumn('suppliers', 'person_type')) {
                $table->dropColumn('person_type');
            }
        });
    }
};
