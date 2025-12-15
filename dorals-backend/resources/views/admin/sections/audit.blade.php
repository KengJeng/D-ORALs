<!-- Audit Trail Section -->
<div id="auditSection" class="hidden p-8 ml-64">

    <div class="bg-white rounded-2xl shadow-sm">

        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">System Audit Trail</h3>
                    <p class="text-sm text-gray-500 mt-1">Track all system activities and user actions</p>
                </div>

                <div class="flex items-center space-x-3">
                    <select id="auditFilter" onchange="filterAuditLogs()"
                            class="px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">All Actions</option>
                        <option value="login">Logins</option>
                        <option value="appointment">Appointments</option>
                        <option value="patient">Patient Actions</option>
                        <option value="service">Service Changes</option>
                    </select>

                    <button onclick="loadAuditLogs()"
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl transition">
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <div class="p-6">

            <!-- Summary Stats -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-xl p-4">
                    <p class="text-sm text-blue-600 font-medium">Total Logs Today</p>
                    <p id="auditTodayCount" class="text-2xl font-bold text-blue-700 mt-1">0</p>
                </div>

                <div class="bg-green-50 rounded-xl p-4">
                    <p class="text-sm text-green-600 font-medium">User Actions</p>
                    <p id="auditUserCount" class="text-2xl font-bold text-green-700 mt-1">0</p>
                </div>

                <div class="bg-purple-50 rounded-xl p-4">
                    <p class="text-sm text-purple-600 font-medium">System Events</p>
                    <p id="auditSystemCount" class="text-2xl font-bold text-purple-700 mt-1">0</p>
                </div>

                <div class="bg-amber-50 rounded-xl p-4">
                    <p class="text-sm text-amber-600 font-medium">Active Users</p>
                    <p id="auditActiveUsers" class="text-2xl font-bold text-amber-700 mt-1">0</p>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Timestamp</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">User ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                        </tr>
                    </thead>

                    <tbody id="auditLogsList">
                        <tr>
                            <td colspan="4" class="text-center py-12 text-gray-400">
                                Loading audit logs...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-6">
                <p class="text-sm text-gray-600">
                    Showing <span id="auditShowing">0</span> of <span id="auditTotal">0</span> logs
                </p>

                <div class="flex space-x-2">
                    <button id="auditPrevBtn"
                            onclick="loadAuditLogs(auditCurrentPage - 1)"
                            class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 disabled:opacity-50"
                            disabled>
                        Previous
                    </button>

                    <button id="auditNextBtn"
                            onclick="loadAuditLogs(auditCurrentPage + 1)"
                            class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 disabled:opacity-50"
                            disabled>
                        Next
                    </button>
                </div>
            </div>

        </div>

    </div>
</div>
