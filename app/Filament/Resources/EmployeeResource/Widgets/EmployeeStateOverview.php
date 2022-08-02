<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use App\Models\Country;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class EmployeeStateOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $us = Country::where('country_code', 'US')->withCount('employees')->first();
        $kr = Country::where('country_code', 'KR')->withCount('employees')->first();
        return [
            Card::make('All Employees', Employee::count()),
            Card::make($us->name . ' Employees', $us->employees_count),
            Card::make($kr->name . ' Employees', $kr->employees_count),
        ];
    }
}
