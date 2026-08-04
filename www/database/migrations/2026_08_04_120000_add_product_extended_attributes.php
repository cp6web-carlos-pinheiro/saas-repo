<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'lifecycle_status')) {
                $table->string('lifecycle_status', 20)->default('ACTIVE')->after('is_active');
            }

            if (! Schema::hasColumn('products', 'technical_attributes')) {
                $table->json('technical_attributes')->nullable()->after('lifecycle_status');
            }

            if (! Schema::hasColumn('products', 'commercial_attributes')) {
                $table->json('commercial_attributes')->nullable()->after('technical_attributes');
            }

            if (! Schema::hasColumn('products', 'fiscal_attributes')) {
                $table->json('fiscal_attributes')->nullable()->after('commercial_attributes');
            }

            if (! Schema::hasColumn('products', 'alternate_uoms')) {
                $table->json('alternate_uoms')->nullable()->after('fiscal_attributes');
            }

            if (! Schema::hasColumn('products', 'image_urls')) {
                $table->json('image_urls')->nullable()->after('alternate_uoms');
            }

            if (! Schema::hasColumn('products', 'attachment_urls')) {
                $table->json('attachment_urls')->nullable()->after('image_urls');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            foreach ([
                'attachment_urls',
                'image_urls',
                'alternate_uoms',
                'fiscal_attributes',
                'commercial_attributes',
                'technical_attributes',
                'lifecycle_status',
            ] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
