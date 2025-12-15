<!-- Queue Section -->
<div id="queueSection" class="p-4 space-y-4 bg-gradient-to-br from-blue-50 via-indigo-50 to-blue-100 min-h-screen">

    {{-- TOP ROW: Calendar (left) + Queue List (right) --}}
    <div class="grid grid-cols-12 gap-4">

        {{-- LEFT: Monthly Calendar --}}
        <div class="col-span-12 xl:col-span-7 bg-white rounded-xl shadow-lg border-l-4 border-blue-500 overflow-hidden">
            <div class="p-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-blue-600">Appointment Load</p>
                            <p id="calendarMonthLabel" class="text-lg font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mt-0.5"></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-xs font-medium">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-green-50 text-green-700 border border-green-200">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span> Light
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Moderate
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-50 text-red-700 border border-red-200">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span> Heavy
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-2">
                    <!-- Day Headers -->
                    <div class="text-center py-2 font-semibold text-xs text-blue-600 border-b-2 border-blue-200">Sun</div>
                    <div class="text-center py-2 font-semibold text-xs text-blue-600 border-b-2 border-blue-200">Mon</div>
                    <div class="text-center py-2 font-semibold text-xs text-blue-600 border-b-2 border-blue-200">Tue</div>
                    <div class="text-center py-2 font-semibold text-xs text-blue-600 border-b-2 border-blue-200">Wed</div>
                    <div class="text-center py-2 font-semibold text-xs text-blue-600 border-b-2 border-blue-200">Thu</div>
                    <div class="text-center py-2 font-semibold text-xs text-blue-600 border-b-2 border-blue-200">Fri</div>
                    <div class="text-center py-2 font-semibold text-xs text-blue-600 border-b-2 border-blue-200">Sat</div>
                </div>

                <div id="appointmentCalendar" class="grid grid-cols-7 gap-2 mt-2">
                </div>
            </div>
        </div>

        {{-- RIGHT: Today's Queue List --}}
        <div class="col-span-12 xl:col-span-5 bg-white rounded-xl shadow-lg border-l-4 border-blue-500 overflow-hidden flex flex-col">
            {{-- Header --}}
            <div class="p-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h3 class="text-base font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Today's Queue
                        </h3>
                        <p class="text-xs text-gray-600 mt-0.5">Real-time appointment queue</p>
                    </div>

                    <button onclick="showNewAppointmentModal()"
                        class="px-3 py-1.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-semibold rounded-lg transition-all shadow-lg shadow-blue-500/30 hover:shadow-xl flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New
                    </button>
                </div>
            </div>

            {{-- Next Patient Card --}}
            <div class="p-5 bg-white">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 rounded-lg flex items-center justify-center shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-gray-900">Next Patient</h4>
                            <p class="text-xs text-gray-600">Ready to be called</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-gray-700 bg-gray-100 px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="currentTimeDisplay" class="font-semibold text-sm">--</span>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 mb-4 shadow-md border-2 border-blue-200">
                    <div id="nextPatient" class="flex items-center justify-center min-h-[80px]">
                        <p class="text-gray-600 text-sm">Loading...</p>
                    </div>
                </div>

                <button onclick="callNextPatient()"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-5 py-4 rounded-xl font-bold text-base transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2 group">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    Call Next Patient
                </button>
            </div>

            {{-- Divider --}}
            <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-y-2 border-blue-200">
                <h5 class="text-xs font-bold text-blue-600 uppercase tracking-wide">Waiting Queue</h5>
            </div>

            {{-- Queue List --}}
            <div class="p-4 flex-1 overflow-y-auto bg-white" style="max-height: 550px;">
                <div id="queueList" class="space-y-2.5">
                    <div class="text-center py-16">
                        <svg class="w-12 h-12 text-blue-300 mx-auto mb-3" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-400 font-medium text-sm">Loading queue...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    /* Calendar styling for proper aspect ratio with reduced padding */
    #appointmentCalendar>div {
        min-height: 90px;
    }
</style>