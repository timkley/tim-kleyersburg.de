<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookmarks', function (Blueprint $table): void {
            $table->unsignedInteger('webpage_id')->nullable()->after('id');
        });
        // database action to move bookmarks information to url
        Illuminate\Support\Facades\DB::table('bookmarks')->get()->each(function ($bookmark): void {
            $id = Illuminate\Support\Facades\DB::table('webpages')->insertGetId([
                'url' => $bookmark->url,
                'favicon' => $bookmark->favicon,
                'title' => $bookmark->title,
                'description' => $bookmark->description,
                'summary' => $bookmark->summary,
                'created_at' => $bookmark->created_at,
                'updated_at' => $bookmark->updated_at,
            ]);

            Illuminate\Support\Facades\DB::table('bookmarks')->where('id', $bookmark->id)->update(['webpage_id' => $id]);
        });

        // update schema, remove unnecessary columns
        Schema::table('bookmarks', function (Blueprint $table): void {
            $table->unsignedInteger('webpage_id')->nullable(false)->change();
            $table->dropColumn('url');
            $table->dropColumn('favicon');
            $table->dropColumn('title');
            $table->dropColumn('description');
            $table->dropColumn('summary');
        });
    }
};
