<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('phone')->nullable()->unique();
            $t->string('role')->default('customer'); // customer | officer | admin
            $t->boolean('is_blocked')->default(false);
            $t->unsignedSmallInteger('failed_attempts')->default(0);
            $t->timestamp('locked_until')->nullable();
        });

        Schema::create('accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('number', 20)->unique();
            $t->string('type')->default('card'); // card | savings
            $t->string('currency', 3)->default('UZS');
            $t->bigInteger('balance')->default(0); // tiyin
            $t->timestamps();
        });

        Schema::create('transactions', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();
            $t->foreignId('from_account_id')->nullable()->constrained('accounts');
            $t->foreignId('to_account_id')->nullable()->constrained('accounts');
            $t->string('external_target')->nullable();
            $t->string('kind'); // internal | external | deposit
            $t->bigInteger('amount'); // tiyin
            $t->string('description')->nullable();
            $t->string('status')->default('completed');
            $t->timestamps();
            $t->index('created_at');
        });

        Schema::create('loan_applications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('type'); // consumer | auto | mortgage | education
            $t->bigInteger('amount'); // tiyin
            $t->unsignedSmallInteger('term_months');
            $t->bigInteger('monthly_income')->nullable();
            $t->string('purpose')->nullable();
            $t->string('status')->default('pending'); // pending | approved | rejected
            $t->foreignId('reviewed_by')->nullable()->constrained('users');
            $t->text('review_note')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action');
            $t->json('meta')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamps();
            $t->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('loan_applications');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('accounts');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['phone', 'role', 'is_blocked', 'failed_attempts', 'locked_until']));
    }
};
