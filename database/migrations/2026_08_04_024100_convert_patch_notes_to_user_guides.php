<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_guides')) {
            return;
        }

        if (Schema::hasTable('patch_notes')) {
            Schema::table('patch_notes', function (Blueprint $table) {
                if (!Schema::hasColumn('patch_notes', 'category')) {
                    $table->string('category')->default('General')->after('title');
                }
                if (!Schema::hasColumn('patch_notes', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0)->after('category');
                }
                if (!Schema::hasColumn('patch_notes', 'content')) {
                    $table->longText('content')->nullable()->after('sort_order');
                }
            });

            foreach (DB::table('patch_notes')->orderBy('id')->get() as $row) {
                DB::table('patch_notes')
                    ->where('id', $row->id)
                    ->update([
                        'content' => $row->description ?? '',
                        'category' => 'General',
                        'sort_order' => (int) $row->id,
                    ]);
            }

            Schema::table('patch_notes', function (Blueprint $table) {
                if (Schema::hasColumn('patch_notes', 'version')) {
                    $table->dropColumn('version');
                }
                if (Schema::hasColumn('patch_notes', 'description')) {
                    $table->dropColumn('description');
                }
            });

            Schema::rename('patch_notes', 'user_guides');

            return;
        }

        Schema::create('user_guides', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('General');
            $table->unsignedInteger('sort_order')->default(0);
            $table->longText('content');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_guides')) {
            return;
        }

        Schema::rename('user_guides', 'patch_notes');

        Schema::table('patch_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('patch_notes', 'version')) {
                $table->string('version')->default('1.0')->after('id');
            }
            if (!Schema::hasColumn('patch_notes', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
        });

        foreach (DB::table('patch_notes')->orderBy('id')->get() as $row) {
            DB::table('patch_notes')
                ->where('id', $row->id)
                ->update([
                    'description' => $row->content ?? '',
                    'version' => '1.0',
                ]);
        }

        Schema::table('patch_notes', function (Blueprint $table) {
            if (Schema::hasColumn('patch_notes', 'content')) {
                $table->dropColumn('content');
            }
            if (Schema::hasColumn('patch_notes', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('patch_notes', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
