<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Tambah kolom ULID (nullable dulu, TANPA unique)
        if (!Schema::hasColumn('tickets', 'ulid')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->ulid('ulid')->nullable()->after('ticket_id')->index();
            });
        }

        // 2) Backfill ULID untuk baris yang masih null / kosong
        //    Pakai chunkById agar hemat memori
        DB::table('tickets')
            ->orderBy('ticket_id')
            ->whereNull('ulid')
            ->orWhere('ulid', '')
            ->chunkById(1000, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('tickets')
                        ->where('ticket_id', $row->ticket_id)
                        ->update(['ulid' => (string) Str::ulid()]);
                }
            }, 'ticket_id');

        // Pastikan tidak ada NULL / '' tersisa (jaga-jaga)
        DB::table('tickets')->whereNull('ulid')->update(['ulid' => (string) Str::ulid()]);
        DB::table('tickets')->where('ulid', '')->update(['ulid' => (string) Str::ulid()]);

        // 3) Barulah pasang UNIQUE index
        //    (cek dulu biar idempotent)
        $this->addUniqueIfMissing('tickets', 'ulid', 'tickets_ulid_unique');
    }

    public function down(): void
    {
        // Lepas unique kalau ada, lalu drop kolom
        if (Schema::hasColumn('tickets', 'ulid')) {
            try {
                Schema::table('tickets', function (Blueprint $table) {
                    $table->dropUnique('tickets_ulid_unique');
                });
            } catch (\Throwable $e) {
                // index belum ada — aman di-skip
            }

            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('ulid');
            });
        }
    }

    private function addUniqueIfMissing(string $table, string $column, string $indexName): void
    {
        // Laravel belum punya API "hasIndex", jadi pakai INFORMATION_SCHEMA
        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();

        if (! $exists) {
            Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
                $table->unique($column, $indexName);
            });
        }
    }
};
