<?php

declare(strict_types=1);

use App\Enums\Holocron\Health\GoalType;
use App\Enums\Holocron\Health\GoalUnit;
use App\Models\Holocron\Health\DailyGoal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('intakes', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->unsignedInteger('amount');
            $table->string('unit');
            $table->timestamps();
        });

        DB::query()
            ->from('water_intakes')
            ->selectRaw('amount, created_at, updated_at')
            ->cursor()
            ->each(function ($waterIntake): void {
                DailyGoal::create([
                    'type' => GoalType::Water,
                    'amount' => $waterIntake->amount,
                    'unit' => GoalUnit::Milliliters,
                    'created_at' => $waterIntake->created_at,
                    'updated_at' => $waterIntake->updated_at,
                ]);
            });

        Schema::drop('water_intakes');
    }
};
