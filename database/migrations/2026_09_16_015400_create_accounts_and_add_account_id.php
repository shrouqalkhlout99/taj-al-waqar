<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $accountTables = ['users', 'students', 'appointments', 'lessons', 'settings'];

    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $accountId = $this->firstAccountId();

        foreach ($this->accountTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->unsignedBigInteger('account_id')->nullable();

                if ($tableName !== 'settings') {
                    $table->index('account_id');
                }
            });
        }

        foreach ($this->accountTables as $tableName) {
            DB::table($tableName)->whereNull('account_id')->update(['account_id' => $accountId]);
        }

        foreach ($this->accountTables as $tableName) {
            $orphans = DB::table($tableName)->whereNull('account_id')->count();

            if ($orphans > 0) {
                throw new RuntimeException("Failed to backfill account_id on {$tableName} ({$orphans} rows).");
            }
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->dropUnique(['key']);
            $table->unique(['account_id', 'key']);
        });

        foreach ($this->accountTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('account_id')->nullable(false)->change();
                $table->foreign('account_id')->references('id')->on('accounts')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->accountTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropForeign(['account_id']);

                if ($tableName === 'settings') {
                    $table->dropUnique(['account_id', 'key']);
                } else {
                    $table->dropIndex(['account_id']);
                }

                $table->dropColumn('account_id');
            });
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->unique('key');
        });

        Schema::dropIfExists('accounts');
    }

    private function firstAccountId(): int
    {
        $existing = DB::table('accounts')->orderBy('id')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        $name = DB::table('users')->orderBy('id')->value('name')
            ?: DB::table('settings')->where('key', 'teacher_name')->value('value')
            ?: 'الحساب المحلي';

        $now = now();

        return (int) DB::table('accounts')->insertGetId([
            'name' => (string) $name,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
