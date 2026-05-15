<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('law_offices', function (Blueprint $table) {
            $table->id();
            $table->string('lawyer');
            $table->string('address');
            $table->string('law_office');
            $table->timestamps();
        });

        $seedRows = DB::table('users')
            ->select('name as lawyer', 'address', 'law_office')
            ->where('role', 'lawyer')
            ->whereNotNull('name')
            ->whereNotNull('address')
            ->whereNotNull('law_office')
            ->where('law_office', '!=', '')
            ->orderBy('name')
            ->get()
            ->unique(function ($row) {
                return strtolower(trim($row->lawyer . '|' . $row->address . '|' . $row->law_office));
            })
            ->map(function ($row) {
                return [
                    'lawyer' => $row->lawyer,
                    'address' => $row->address,
                    'law_office' => $row->law_office,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->values()
            ->all();

        if (!empty($seedRows)) {
            DB::table('law_offices')->insert($seedRows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('law_offices');
    }
};
