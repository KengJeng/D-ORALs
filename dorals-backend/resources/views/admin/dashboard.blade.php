<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D-ORALS Admin Dashboard</title>

    {{-- Tailwind & Chart.js from CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
      integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer" />

    <style>
        /* Custom styles for purple-blue gradient theme */
        .nav-item {
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
        }
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-purple-50 via-blue-50 to-purple-100 min-h-screen">

    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 h-full w-64 bg-gradient-to-b from-purple-600 via-purple-700 to-blue-600 shadow-xl z-40">
        <div class="p-6 border-b border-purple-500">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">DENTI-ORAL</h1>
                    <p class="text-xs text-purple-200">Admin Portal</p>
                </div>
            </div>
        </div>

        <nav class="p-4 space-y-1">
            <a href="#" onclick="showSection('queue')"
               class="nav-item active flex items-center space-x-3 px-4 py-3 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                    <path fill-rule="evenodd"
                          d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" />
                </svg>
                <span class="font-medium">Appointments & Queue</span>
            </a>

            <a href="#" onclick="showSection('patients')"
               class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                </svg>
                <span class="font-medium">View Patients</span>
            </a>

            <a href="#" onclick="showSection('services')"
               class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" />
                </svg>
                <span class="font-medium">Manage Services</span>
            </a>

            <a href="#" onclick="showSection('reports')"
               class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                </svg>
                <span class="font-medium">Reports & Analytics</span>
            </a>

            <a href="#" onclick="showSection('audit')"
               class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" />
                </svg>
                <span class="font-medium">Audit Trail</span>
            </a>

            <a href="#" onclick="showSection('settings')"
               class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" />
                </svg>
                <span class="font-medium">Settings</span>
            </a>
        </nav>

        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-purple-500">
            <div class="flex items-center justify-between px-4 py-3 bg-white/10 backdrop-blur-sm rounded-xl">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-purple-600 font-bold text-sm">
                        A
                    </div>
                    <div>
                        <p id="sidebarAdminName" class="text-sm font-semibold text-white">Admin</p>
                        <p class="text-xs text-purple-200">Administrator</p>
                    </div>
                </div>
                <button onclick="logout()" class="text-purple-200 hover:text-white transition">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" />
                    </svg>
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="ml-64">
        <!-- Top Bar -->
        <header class="bg-white/80 backdrop-blur-sm border-b border-purple-100 sticky top-0 z-30">
            <div class="px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 id="pageTitle" class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                            Appointments & Queue
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Manage today's appointments and patient queue</p>
                    </div>
                    <button onclick="loadQueue()"
                            class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white rounded-xl transition flex items-center space-x-2 shadow-lg shadow-purple-500/30">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" />
                        </svg>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Alert -->
        <div id="alert" class="hidden mx-8 mt-6"></div>

        {{-- Sections --}}
        @include('admin.sections.queue')
        @include('admin.sections.patients')
        @include('admin.sections.services')
        @include('admin.sections.reports')
        @include('admin.sections.settings')
    </div>

    {{-- Audit Section (kept outside to preserve ml-64 structure if you prefer) --}}
    @include('admin.sections.audit')

    {{-- Modals --}}
    @include('admin.modals.new-appointment')
    @include('admin.modals.edit-patient')

    
    {{-- Report & forecast data exposed to JS --}}
<script>
    window.reportChartsData = {
        historicalLabels:      @json($historicalLabels ?? []),
        historicalValues:      @json($historicalValues ?? []),

        forecastLabels:        @json($forecastLabels ?? []),
        forecastValues:        @json($forecastValues ?? []),

        combinedLabels:        @json($combinedLabels ?? []),
        combinedHistorical:    @json($combinedHistorical ?? []),
        combinedForecast:      @json($combinedForecast ?? []),

        serviceNames:          @json($serviceForecast['labels'] ?? []),
        serviceCounts:         @json($serviceForecast['values'] ?? []),
    };
</script>
{{-- Main JS --}}
<script type="module" src="/js/admin/index.js"></script>

</body>
</html>