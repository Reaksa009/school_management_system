<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('recorded_by');
            $table->string('transaction_id')->nullable()->after('status');
            $table->string('verification_status')->default('pending')->after('transaction_id');
            $table->text('verification_error')->nullable()->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('verification_error');
            $table->longText('khqr_payload')->nullable()->after('verified_at');
            $table->string('khqr_md5')->nullable()->index()->after('khqr_payload');
            $table->timestamp('khqr_expires_at')->nullable()->after('khqr_md5');
            $table->timestamp('submitted_at')->nullable()->after('khqr_expires_at');
            $table->timestamp('confirmed_at')->nullable()->after('submitted_at');
            $table->json('meta')->nullable()->after('confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'provider',
                'transaction_id',
                'verification_status',
                'verification_error',
                'verified_at',
                'khqr_payload',
                'khqr_md5',
                'khqr_expires_at',
                'submitted_at',
                'confirmed_at',
                'meta',
            ]);
        });
    }
};
