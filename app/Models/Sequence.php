<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sequence extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'name';

    protected $keyType = 'string';

    protected $fillable = ['name', 'value'];

    protected $table = 'sequences';

    /**
     * Get next sequence value in concurrency-safe transaction.
     */
    public static function next(string $name): int
    {
        return DB::transaction(function () use ($name) {
            $row = DB::table('sequences')->where('name', $name)->lockForUpdate()->first();

            if (! $row) {
                DB::table('sequences')->insert(['name' => $name, 'value' => 1, 'created_at' => now(), 'updated_at' => now()]);

                return 1;
            }

            $next = $row->value + 1;
            DB::table('sequences')->where('name', $name)->update(['value' => $next, 'updated_at' => now()]);

            return (int) $next;
        });
    }
}
