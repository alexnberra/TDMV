<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $hasTribeId = Schema::hasColumn('users', 'tribe_id');
        $hasTribalEnrollmentId = Schema::hasColumn('users', 'tribal_enrollment_id');
        $hasFirstName = Schema::hasColumn('users', 'first_name');
        $hasLastName = Schema::hasColumn('users', 'last_name');
        $hasMiddleName = Schema::hasColumn('users', 'middle_name');
        $hasDateOfBirth = Schema::hasColumn('users', 'date_of_birth');
        $hasPhone = Schema::hasColumn('users', 'phone');
        $hasPhoneVerifiedAt = Schema::hasColumn('users', 'phone_verified_at');
        $hasRole = Schema::hasColumn('users', 'role');
        $hasAddressLine1 = Schema::hasColumn('users', 'address_line1');
        $hasAddressLine2 = Schema::hasColumn('users', 'address_line2');
        $hasCity = Schema::hasColumn('users', 'city');
        $hasState = Schema::hasColumn('users', 'state');
        $hasZipCode = Schema::hasColumn('users', 'zip_code');
        $hasIsActive = Schema::hasColumn('users', 'is_active');
        $hasLastLoginAt = Schema::hasColumn('users', 'last_login_at');
        $hasDeletedAt = Schema::hasColumn('users', 'deleted_at');
        $hasCompoundIndex = $this->indexExists('users', 'users_email_enroll_tribe_role_idx');

        Schema::table('users', function (Blueprint $table) use (
            $hasTribeId,
            $hasTribalEnrollmentId,
            $hasFirstName,
            $hasLastName,
            $hasMiddleName,
            $hasDateOfBirth,
            $hasPhone,
            $hasPhoneVerifiedAt,
            $hasRole,
            $hasAddressLine1,
            $hasAddressLine2,
            $hasCity,
            $hasState,
            $hasZipCode,
            $hasIsActive,
            $hasLastLoginAt,
            $hasDeletedAt,
            $hasCompoundIndex
        ): void {
            if (! $hasTribeId) {
                $table->foreignId('tribe_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (! $hasTribalEnrollmentId) {
                $table->string('tribal_enrollment_id')->nullable()->unique()->after('tribe_id');
            }

            if (! $hasFirstName) {
                $table->string('first_name')->nullable()->after('name');
            }

            if (! $hasLastName) {
                $table->string('last_name')->nullable()->after('first_name');
            }

            if (! $hasMiddleName) {
                $table->string('middle_name')->nullable()->after('last_name');
            }

            if (! $hasDateOfBirth) {
                $table->date('date_of_birth')->nullable()->after('middle_name');
            }

            if (! $hasPhone) {
                $table->string('phone')->nullable()->after('email_verified_at');
            }

            if (! $hasPhoneVerifiedAt) {
                $table->timestamp('phone_verified_at')->nullable()->after('phone');
            }

            if (! $hasRole) {
                $table->enum('role', ['member', 'staff', 'admin'])->default('member')->after('password');
            }

            if (! $hasAddressLine1) {
                $table->string('address_line1')->nullable()->after('role');
            }

            if (! $hasAddressLine2) {
                $table->string('address_line2')->nullable()->after('address_line1');
            }

            if (! $hasCity) {
                $table->string('city')->nullable()->after('address_line2');
            }

            if (! $hasState) {
                $table->string('state')->nullable()->after('city');
            }

            if (! $hasZipCode) {
                $table->string('zip_code')->nullable()->after('state');
            }

            if (! $hasIsActive) {
                $table->boolean('is_active')->default(true)->after('zip_code');
            }

            if (! $hasLastLoginAt) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }

            if (! $hasDeletedAt) {
                $table->softDeletes()->after('remember_token');
            }

            if (! $hasCompoundIndex) {
                $table->index(['email', 'tribal_enrollment_id', 'tribe_id', 'role'], 'users_email_enroll_tribe_role_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $hasCompoundIndex = $this->indexExists('users', 'users_email_enroll_tribe_role_idx');
        $hasTribeId = Schema::hasColumn('users', 'tribe_id');
        $columns = array_values(array_filter([
            Schema::hasColumn('users', 'tribal_enrollment_id') ? 'tribal_enrollment_id' : null,
            Schema::hasColumn('users', 'first_name') ? 'first_name' : null,
            Schema::hasColumn('users', 'last_name') ? 'last_name' : null,
            Schema::hasColumn('users', 'middle_name') ? 'middle_name' : null,
            Schema::hasColumn('users', 'date_of_birth') ? 'date_of_birth' : null,
            Schema::hasColumn('users', 'phone') ? 'phone' : null,
            Schema::hasColumn('users', 'phone_verified_at') ? 'phone_verified_at' : null,
            Schema::hasColumn('users', 'role') ? 'role' : null,
            Schema::hasColumn('users', 'address_line1') ? 'address_line1' : null,
            Schema::hasColumn('users', 'address_line2') ? 'address_line2' : null,
            Schema::hasColumn('users', 'city') ? 'city' : null,
            Schema::hasColumn('users', 'state') ? 'state' : null,
            Schema::hasColumn('users', 'zip_code') ? 'zip_code' : null,
            Schema::hasColumn('users', 'is_active') ? 'is_active' : null,
            Schema::hasColumn('users', 'last_login_at') ? 'last_login_at' : null,
            Schema::hasColumn('users', 'deleted_at') ? 'deleted_at' : null,
        ]));

        Schema::table('users', function (Blueprint $table) use ($hasCompoundIndex, $hasTribeId, $columns): void {
            if ($hasCompoundIndex) {
                $table->dropIndex('users_email_enroll_tribe_role_idx');
            }

            if ($hasTribeId) {
                $table->dropConstrainedForeignId('tribe_id');
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            return DB::table('information_schema.statistics')
                ->whereRaw('table_schema = database()')
                ->where('table_name', $table)
                ->where('index_name', $index)
                ->exists();
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $item) {
                $name = is_array($item) ? ($item['name'] ?? null) : ($item->name ?? null);

                if ($name === $index) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'pgsql') {
            return DB::table('pg_indexes')
                ->where('schemaname', 'public')
                ->where('tablename', $table)
                ->where('indexname', $index)
                ->exists();
        }

        return false;
    }
};
