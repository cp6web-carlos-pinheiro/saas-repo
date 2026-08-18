<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('name', 180);
            $table->string('slug', 180)->unique();
            $table->string('domain', 180)->nullable()->index();
            $table->string('segment', 120)->nullable();
            $table->string('operation_size', 80)->nullable();
            $table->string('timezone', 80)->default('UTC');
            $table->json('preferences')->nullable();
            $table->timestamps();
        });

        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name', 180);
            $table->string('slug', 180)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('trials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->dateTime('trial_start_date');
            $table->dateTime('trial_end_date');
            $table->dateTime('grace_ends_at')->nullable();
            $table->string('status', 40)->default('active')->index();
            $table->timestamp('expired_at')->nullable();
            $table->boolean('is_expired')->default(false)->index();
            $table->string('email_domain', 180)->nullable()->index();
            $table->ipAddress('registration_ip')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['organization_id', 'status']);
        });

        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('trial_id')->nullable()->constrained('trials')->nullOnDelete();
            $table->string('provider', 40)->default('stripe');
            $table->string('provider_customer_id', 120)->nullable()->index();
            $table->string('provider_subscription_id', 120)->nullable()->index();
            $table->string('plan_code', 80)->default('trial');
            $table->string('status', 40)->default('trialing')->index();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('onboarding_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('segment', 120)->nullable();
            $table->string('operation_size', 80)->nullable();
            $table->string('timezone', 80)->nullable();
            $table->boolean('import_data')->default(false);
            $table->boolean('connect_integrations')->default(false);
            $table->boolean('invite_team')->default(false);
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
        });

        Schema::create('social_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('provider_user_id', 180);
            $table->string('email', 190)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_user_id']);
            $table->index(['user_id', 'provider']);
        });

        Schema::create('email_verifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token', 128)->unique();
            $table->dateTime('expires_at');
            $table->dateTime('verified_at')->nullable();
            $table->ipAddress('requested_ip')->nullable();
            $table->timestamps();
        });

        Schema::create('password_resets', function (Blueprint $table): void {
            $table->id();
            $table->string('email', 190)->index();
            $table->string('token', 128);
            $table->dateTime('expires_at');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('event', 120)->index();
            $table->string('severity', 20)->default('info');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('email_verifications');
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('onboarding_profiles');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('trials');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('organizations');
    }
};
