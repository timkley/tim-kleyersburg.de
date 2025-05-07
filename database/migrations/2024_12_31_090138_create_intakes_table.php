<?php

declare(strict_types=1);

use App\Enums\Holocron\Health\GoalTypes;
use App\Enums\Holocron\Health\GoalUnits;
use App\Models\Holocron\Health\DailyGoal;
use App\Models\Holocron\Health\Intake;
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
                    'type' => GoalTypes::Water,
                    'amount' => $waterIntake->amount,
                    'unit' => GoalUnits::Milliliters,
                    'created_at' => $waterIntake->created_at,
                    'updated_at' => $waterIntake->updated_at,
                ]);
            });

        Schema::drop('water_intakes');
    }
};
