<!-- Patients Section -->
<div id="patientsSection" class="hidden p-8">

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <!-- Search -->
        <div class="mb-6">
            <input type="text"
                   id="patientSearch"
                   placeholder="Search patients by name or email..."
                   onkeyup="searchPatients()"
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- List -->
        <div id="patientsList" class="space-y-3">
            <p class="text-gray-400 text-center py-12">Loading patients...</p>
        </div>

        <!-- Pagination -->
        <div id="patientsPagination"
             class="flex items-center justify-between mt-6 pt-6 border-t border-gray-200">

            <div class="text-sm text-gray-600">
                Showing <span id="patientsFrom">0</span> to <span id="patientsTo">0</span>
                of <span id="patientsTotal">0</span> patients
            </div>

            <div class="flex gap-2">
                <button onclick="goToPatientsPage('prev')" id="patientsPrevBtn"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>

                <div id="patientsPageNumbers" class="flex gap-2"></div>

                <button onclick="goToPatientsPage('next')" id="patientsNextBtn"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        </div>
    </div>

</div>
