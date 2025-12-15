<!-- New Appointment Modal -->
<div id="newAppointmentModal"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-white">
            <h3 class="text-2xl font-bold text-gray-800">New Appointment</h3>
            <button onclick="closeModal('newAppointmentModal')"
                    class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                    </svg>
            </button>
        </div>
        <form id="newAppointmentForm" class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Patient</label>
                <select id="appointmentPatient" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Loading...</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Date</label>
                <input type="date" id="appointmentDate" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Services</label>
                <div id="appointmentServices"
                     class="border border-gray-200 rounded-xl p-4 max-h-60 overflow-y-auto">
                    <p class="text-gray-400">Loading...</p>
                </div>
            </div>
            <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white py-4 rounded-xl font-semibold transition shadow-lg shadow-blue-500/30">
                Create Appointment
            </button>
        </form>
    </div>
</div>
