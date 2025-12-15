<!-- Reports & Analytics Section -->
<div id="reportsSection" class="hidden p-8">

    <!-- Header + Export with Range Dropdown -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                Reports & Analytics
            </h2>
            <p class="text-sm text-gray-600">
                Download appointment data for a specific period.
            </p>
        </div>

        <div class="flex items-center gap-3">
            {{-- Range selector --}}
            <select id="exportRange"
                class="px-3 py-2 text-sm border border-purple-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                <option value="today">Today</option>
                <option value="last7">Last 7 Days</option>
                <option value="thisMonth">This Month</option>
                <option value="all">All Time</option>
            </select>

            {{-- Export Appointments (Excel/CSV) --}}
            <button type="button" data-export-url="{{ route('reports.appointments.export') }}"
                onclick="exportAppointments(this)"
                class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl bg-gradient-to-r from-purple-600 to-blue-600 text-white shadow-lg shadow-purple-500/30 hover:from-purple-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 10.5L12 15m0 0L7.5 10.5M12 15V3" />
                </svg>
                <span class="ml-2">Export Appointments</span>
            </button>
        </div>
    </div>


    <!-- Descriptive Analysis Section -->
    <!-- Summary Cards -->
    <div class="grid grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-white to-purple-50 rounded-2xl p-6 shadow-lg border-l-4 border-purple-500">
            <p class="text-sm text-gray-600 font-medium">Total Appointments</p>
            <p id="reportTotalApts" class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent mt-2">0</p>
            <p class="text-xs text-gray-500 mt-1">All time</p>
        </div>

        <div class="bg-gradient-to-br from-white to-green-50 rounded-2xl p-6 shadow-lg border-l-4 border-green-500">
            <p class="text-sm text-gray-600 font-medium">Completion Rate</p>
            <p id="reportCompletionRate" class="text-3xl font-bold text-green-600 mt-2">0%</p>
            <p class="text-xs text-gray-500 mt-1">Successfully completed</p>
        </div>

        <div class="bg-gradient-to-br from-white to-red-50 rounded-2xl p-6 shadow-lg border-l-4 border-red-500">
            <p class="text-sm text-gray-600 font-medium">Cancellation Rate</p>
            <p id="reportCancelRate" class="text-3xl font-bold text-red-600 mt-2">0%</p>
            <p class="text-xs text-gray-500 mt-1">Canceled + No-show</p>
        </div>

        <div class="bg-gradient-to-br from-white to-purple-50 rounded-2xl p-6 shadow-lg border-l-4 border-purple-500">
            <p class="text-sm text-gray-600 font-medium">Avg Per Day</p>
            <p id="reportAvgPerDay" class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent mt-2">0</p>
            <p class="text-xs text-gray-500 mt-1">Daily average</p>
        </div>
    </div>

    <!-- Demographics Section -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent mb-6">
            Patient Demographics
        </h2>
    </div>

    <div class="grid grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl p-6 shadow-lg border-l-4 border-blue-500">
            <p class="text-sm text-gray-600 font-medium">Total Patients</p>
            <p id="demoTotalPatients" class="text-3xl font-bold text-blue-600 mt-2">0</p>
            <p class="text-xs text-gray-500 mt-1">Registered</p>
        </div>

        <div class="bg-gradient-to-br from-white to-green-50 rounded-2xl p-6 shadow-lg border-l-4 border-green-500">
            <p class="text-sm text-gray-600 font-medium">New This Month</p>
            <p id="demoNewThisMonth" class="text-3xl font-bold text-green-600 mt-2">0</p>
            <p class="text-xs text-gray-500 mt-1">New registrations</p>
        </div>

        <div class="bg-gradient-to-br from-white to-purple-50 rounded-2xl p-6 shadow-lg border-l-4 border-purple-500">
            <p class="text-sm text-gray-600 font-medium">Active Patients</p>
            <p id="demoActivePatients" class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent mt-2">0</p>
            <p class="text-xs text-gray-500 mt-1">With appointments</p>
        </div>

        <div class="bg-gradient-to-br from-white to-amber-50 rounded-2xl p-6 shadow-lg border-l-4 border-amber-500">
            <p class="text-sm text-gray-600 font-medium">Avg Visits</p>
            <p id="demoAvgVisits" class="text-3xl font-bold text-amber-600 mt-2">0</p>
            <p class="text-xs text-gray-500 mt-1">Per patient</p>
        </div>
    </div>

    <!-- Demographics Charts -->
    <div class="grid grid-cols-2 gap-6 mb-8">
        <div class="bg-gradient-to-br from-white to-purple-50 rounded-2xl shadow-lg p-6 border-l-4 border-purple-500">
            <h3 class="text-lg font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent mb-4">
                Gender Distribution
            </h3>
            <div class="flex justify-center items-center">
                <canvas id="genderChart" style="max-width: 250px; max-height: 250px;"></canvas>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-lg p-6 border-l-4 border-blue-500">
            <h3 class="text-lg font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent mb-4">
                Top Barangays (Top 10)
            </h3>
            <canvas id="barangayChart"></canvas>
        </div>
    </div>

    <!-- Predictive Analysis Section -->
    <div class="grid grid-cols-2 gap-6 mb-8">
        <!-- Combined Historical + Forecast Chart -->
        <div class="bg-gradient-to-br from-white to-purple-50 rounded-2xl shadow-lg border-l-4 border-purple-500 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                        Forecasted Daily Appointments
                    </h3>
                    <p class="text-xs text-gray-500">Combined historical (solid) and forecast (dashed) trend line.</p>
                </div>

                <div class="flex gap-4 text-xs text-gray-600">
                    <div class="flex items-center gap-1">
                        <span class="w-4 h-[3px] bg-purple-500 block rounded"></span> Historical
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-4 h-[3px] border border-dashed border-rose-500 block rounded"></span> Forecast
                    </div>
                </div>
            </div>

            <div class="w-full h-80">
                <canvas id="combinedForecastChart"></canvas>
            </div>
        </div>

        <!-- Forecasted Service Demand -->
        <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-lg border-l-4 border-blue-500 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                        Forecasted Service Demand
                    </h3>
                    <p class="text-xs text-gray-500">Expected service volume for the next forecast window.</p>
                </div>
            </div>

            <div class="w-full h-72">
                <canvas id="serviceForecastChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Prescriptive Analysis Section -->
    <div class="mt-12 bg-gradient-to-br from-white to-purple-50 border-l-4 border-purple-500 shadow-xl rounded-2xl p-8">
        <h2 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent mb-2">
            Smart Appointment Scheduling Recommendations
        </h2>
        <p class="text-gray-600 text-sm mb-6">
            Actionable decisions generated from forecasted volume, historical patterns, and no-show risks.
        </p>

        <!-- Main Insight Box -->
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 border-2 border-purple-200 p-6 rounded-xl mb-8 shadow-lg">
            <h3 class="font-semibold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent text-lg mb-2">
                System Insight
            </h3>
            <p class="text-base text-gray-700 leading-relaxed">{{ $prescriptiveReco['message'] ?? '' }}</p>
        </div>

        <!-- 2x2 Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="p-6 rounded-xl border-2 border-purple-200 bg-gradient-to-br from-white to-purple-50 shadow-lg">
                <p class="text-xs text-purple-600 uppercase font-semibold mb-1">Predicted Daily Load</p>
                <p class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                    {{ $prescriptiveReco['avg_daily_load'] }}
                </p>
            </div>

            <div class="p-6 rounded-xl bg-gradient-to-br from-green-50 to-emerald-100 border-2 border-green-300 shadow-lg">
                <p class="text-xs text-green-700 uppercase font-semibold mb-1">Recommended Day</p>
                <p class="text-4xl font-extrabold text-green-700">{{ $prescriptiveReco['suggested_day'] }}</p>
            </div>

            <div class="p-6 rounded-xl border-2 border-red-200 bg-gradient-to-br from-white to-red-50 shadow-lg">
                <p class="text-xs text-red-600 uppercase font-semibold mb-1">No-Show Risk</p>
                <p class="text-4xl font-bold text-red-600">{{ $prescriptiveReco['no_show_risk'] }}%</p>
            </div>

            <div class="p-6 rounded-xl bg-gradient-to-br from-red-50 to-rose-100 border-2 border-red-300 shadow-lg">
                <p class="text-xs text-red-700 uppercase font-semibold mb-1">Worst Day</p>
                <p class="text-4xl font-extrabold text-red-600">{{ $prescriptiveReco['worst_day'] }}</p>
            </div>
        </div>

        <div class="mt-8 bg-white p-6 rounded-2xl shadow-lg border-2 border-purple-200">
            <h3 class="text-lg font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent mb-4">
                Day-of-Week Performance Ranking
            </h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-600 border-b-2 border-purple-200">
                        <th class="py-3 text-left font-semibold">Rank</th>
                        <th class="py-3 text-left font-semibold">Day</th>
                        <th class="py-3 text-center font-semibold">Avg Load</th>
                        <th class="py-3 text-center font-semibold">No-Show Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($weekdayRanking as $i => $row)
                        <tr class="border-t border-purple-100 hover:bg-purple-50 transition">
                            <td class="py-3 font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                                {{ $i + 1 }}
                            </td>
                            <td class="py-3 font-medium text-gray-700">{{ $row['day'] }}</td>
                            <td class="py-3 text-center font-semibold text-gray-700">{{ $row['load'] }}</td>
                            <td class="py-3 text-center font-semibold text-gray-700">{{ $row['no_show_rate'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>