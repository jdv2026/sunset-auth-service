<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AnalyticPageView;
use Carbon\Carbon;

class AnalyticPageViewFactory extends Factory
{
    protected $model = AnalyticPageView::class;

    public function definition()
    {
        // default if used outside sequence
        $date = now()->startOfMonth();

        return [
            'name' => 'page_views',
            'value' => $this->faker->numberBetween(0, 500),
            'date' => $date->format('Y-m-d'),
        ];
    }

    /**
     * Generate sequential months in chronological order
     */
    public function lastMonths(int $months = 6)
    {
        return $this->state(function (array $attributes, $sequence) use ($months) {
            // sequence = 0 .. count-1
            // oldest month first
            $monthOffset = $months - $sequence - 1;
            $date = now()->subMonths($monthOffset)->startOfMonth();

            return [
                'date' => $date->format('Y-m-d'),
            ];
        });
    }
}