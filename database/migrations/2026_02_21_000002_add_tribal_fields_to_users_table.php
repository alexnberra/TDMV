<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('tribe_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('tribal_enrollment_id')->nullable()->unique()->after('tribe_id');
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('middle_name')->nullable()->after('last_name');
            $table->date('date_of_birth')->nullable()->after('middle_name');
            $table->string('phone')->nullable()->after('email_verified_at');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->enum('role', ['member', 'staff', 'admin'])->default('member')->after('password');
            $table->string('address_line1')->nullable()->after('role');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('city')->nullable()->after('address_line2');
            $table->string('state')->nullable()->after('city');
            $table->string('zip_code')->nullable()->after('state');
            $table->boolean('is_active')->default(true)->after('zip_code');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->softDeletes()->after('remember_token');

            $table->index(['email', 'tribal_enrollment_id', 'tribe_id', 'role'], 'users_email_enroll_tribe_role_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_email_enroll_tribe_role_idx');
            $table->dropConstrainedForeignId('tribe_id');
            $table->dropColumn([
                'tribal_enrollment_id',
                'first_name',
                'last_name',
                'middle_name',
                'date_of_birth',
                'phone',
                'phone_verified_at',
                'role',
                'address_line1',
                'address_line2',
                'city',
                'state',
                'zip_code',
                'is_active',
                'last_login_at',
                'deleted_at',
            ]);
        });
    }
};
