<!-- Edit Patient Modal -->
<div id="editPatientModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-white">
            <h3 class="text-2xl font-bold text-gray-800">Edit Patient</h3>
            <button onclick="closeModal('editPatientModal')" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                </svg>
            </button>
        </div>
        <form id="editPatientForm" class="p-6 space-y-4">
            <input type="hidden" id="editPatientId">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">First name</label>
                <input id="editFirstName" type="text" class="w-full px-4 py-3 border border-gray-200 rounded-xl" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Middle name</label>
                <input id="editMiddleName" type="text" class="w-full px-4 py-3 border border-gray-200 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Last name</label>
                <input id="editLastName" type="text" class="w-full px-4 py-3 border border-gray-200 rounded-xl" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input id="editEmail" type="email" class="w-full px-4 py-3 border border-gray-200 rounded-xl" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Contact No.</label>
                <input id="editContact" type="text" class="w-full px-4 py-3 border border-gray-200 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Sex</label>
                <select id="editSex" class="w-full px-4 py-3 border border-gray-200 rounded-xl">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                <textarea id="editAddress" class="w-full px-4 py-3 border border-gray-200 rounded-xl" rows="3"></textarea>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="submitEditPatient()" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-xl">Save</button>
                <button type="button" onclick="closeModal('editPatientModal')" class="flex-1 bg-gray-200 hover:bg-gray-300 px-4 py-3 rounded-xl">Cancel</button>
            </div>
        </form>
    </div>
</div>
