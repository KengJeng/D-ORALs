<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - D-ORALS</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/>
                </svg>
                <span class="text-2xl font-bold">D-ORALS</span>
            </div>
            <div class="flex items-center space-x-4">
                <span id="patientName" class="text-sm">Patient</span>
                <a href="/patient/appointments" class="bg-green-500 hover:bg-green-400 px-4 py-2 rounded text-sm transition duration-200">
                    My Appointments
                </a>
                <a href="/patient/dashboard/view" class="bg-blue-500 hover:bg-blue-400 px-4 py-2 rounded text-sm transition duration-200">
                    Dashboard
                </a>
                <button onclick="logout()" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm">
                    Logout
                </button>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Book an Appointment</h1>
                <p class="text-gray-600">Schedule your dental visit with us</p>
            </div>

            <!-- Alert Message -->
            <div id="alert" class="hidden mb-6 p-4 rounded-lg"></div>

            <!-- Appointment Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <form id="appointmentForm" class="space-y-6">
                    <!-- Date Selection -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Appointment Date *</label>
                        <input type="date" id="scheduled_date" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-gray-500 text-sm mt-1">Select a future date</p>
                    </div>

                    <!-- Service Selection -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Select Services *</label>
                        <div id="servicesList" class="space-y-2 border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto">
                            <p class="text-gray-500 text-center py-4">Loading services...</p>
                        </div>
                        <p class="text-gray-500 text-sm mt-1">Select at least one service</p>
                    </div>

                    <!-- Selected Services Summary -->
                    <div id="selectedSummary" class="hidden">
                        <label class="block text-gray-700 font-medium mb-2">Selected Services</label>
                        <div id="selectedList" class="bg-blue-50 rounded-lg p-4">
                            <!-- Selected services will appear here -->
                        </div>
                        <p id="totalDuration" class="text-gray-600 text-sm mt-2"></p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex space-x-4">
                        <button type="submit" id="submitBtn"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-200">
                            Book Appointment
                        </button>
                        <a href="/patient/dashboard"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-lg text-center transition duration-200">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- My Appointments -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">My Appointments</h2>
                <div id="appointmentsList">
                    <p class="text-gray-500 text-center py-4">Loading appointments...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = 'http://127.0.0.1:8000/api'; // Correct

        let services = [];
        let selectedServices = [];
        let patientToken = localStorage.getItem('patient_token');
        let patient = JSON.parse(localStorage.getItem('patient') || 'null');

        // Check authentication - wait for DOM to be ready
        if (!patientToken || !patient) {
            console.warn('No authentication token or patient data found');
            window.location.href = '/patient/login';
        }

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('scheduled_date').setAttribute('min', today);

        // Display patient name
        if (patient && patient.first_name) {
            document.getElementById('patientName').textContent = patient.first_name;
        }

        // Load services
        async function loadServices() {
            try {
                const response = await fetch(`${API_BASE}/services`);
                services = await response.json();
                displayServices();
            } catch (error) {
                document.getElementById('servicesList').innerHTML = 
                    '<p class="text-red-500 text-center">Failed to load services</p>';
            }
        }

        function displayServices() {
            const container = document.getElementById('servicesList');
            container.innerHTML = services.map(service => `
                <label class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded cursor-pointer">
                    <input type="checkbox" value="${service.service_id}" 
                        onchange="toggleService(${service.service_id})"
                        class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                    <div class="flex-1">
                        <p class="font-medium text-gray-800">${service.name}</p>
                        <p class="text-sm text-gray-500">${service.duration} minutes</p>
                    </div>
                </label>
            `).join('');
        }

        function toggleService(serviceId) {
            const service = services.find(s => s.service_id === serviceId);
            const index = selectedServices.findIndex(s => s.service_id === serviceId);

            if (index > -1) {
                selectedServices.splice(index, 1);
            } else {
                selectedServices.push(service);
            }

            updateSelectedSummary();
        }

        function updateSelectedSummary() {
            const summary = document.getElementById('selectedSummary');
            const list = document.getElementById('selectedList');
            const duration = document.getElementById('totalDuration');

            if (selectedServices.length === 0) {
                summary.classList.add('hidden');
                return;
            }

            summary.classList.remove('hidden');
            list.innerHTML = selectedServices.map(s => 
                `<div class="flex justify-between items-center py-2">
                    <span class="font-medium">${s.name}</span>
                    <span class="text-gray-600">${s.duration} min</span>
                </div>`
            ).join('');

            const totalMin = selectedServices.reduce((sum, s) => sum + s.duration, 0);
            duration.textContent = `Total estimated time: ${totalMin} minutes`;
        }

        // Submit appointment
        document.getElementById('appointmentForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            if (selectedServices.length === 0) {
                showAlert('Please select at least one service', 'error');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Booking...';

            const formData = {
                patient_id: patient.patient_id,
                scheduled_date: document.getElementById('scheduled_date').value,
                service_ids: selectedServices.map(s => s.service_id),
            };

            try {
                const response = await fetch(`${API_BASE}/appointments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${patientToken}`,
                    },
                    body: JSON.stringify(formData),
                });

                const data = await response.json();

                if (response.ok) {
                    showAlert(`Appointment booked successfully! Your queue number is #${data.appointment.queue_number}`, 'success');
                    document.getElementById('appointmentForm').reset();
                    selectedServices = [];
                    updateSelectedSummary();
                    loadAppointments();
                } else {
                    showAlert(data.message || 'Failed to book appointment', 'error');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Book Appointment';
            }
        });

        // Load my appointments
        async function loadAppointments() {
            try {
                const response = await fetch(`${API_BASE}/appointments/my`, {
                    headers: {
                        'Authorization': `Bearer ${patientToken}`,
                    },
                });

                const data = await response.json();
                displayAppointments(data.data || []);
            } catch (error) {
                document.getElementById('appointmentsList').innerHTML = 
                    '<p class="text-red-500 text-center">Failed to load appointments</p>';
            }
        }

        function displayAppointments(appointments) {
            const container = document.getElementById('appointmentsList');
            
            if (appointments.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No appointments yet</p>';
                return;
            }

            container.innerHTML = appointments.map(apt => `
                <div class="border border-gray-200 rounded-lg p-4 mb-3">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-bold text-lg">Queue #${apt.queue_number || 'TBD'}</p>
                            <p class="text-gray-600">${new Date(apt.scheduled_date).toLocaleDateString()}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(apt.status)}">
                            ${apt.status}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600">
                        <p><strong>Services:</strong> ${apt.services?.map(s => s.name).join(', ') || 'N/A'}</p>
                    </div>
                </div>
            `).join('');
        }

        function getStatusColor(status) {
            const colors = {
                'Pending': 'bg-yellow-100 text-yellow-800',
                'Confirmed': 'bg-blue-100 text-blue-800',
                'Completed': 'bg-green-100 text-green-800',
                'Canceled': 'bg-red-100 text-red-800',
                'No-show': 'bg-gray-100 text-gray-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }

        function showAlert(message, type) {
            const alert = document.getElementById('alert');
            alert.className = `mb-6 p-4 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
            alert.textContent = message;
            alert.classList.remove('hidden');
            setTimeout(() => alert.classList.add('hidden'), 5000);
        }

        function logout() {
            localStorage.removeItem('patient_token');
            localStorage.removeItem('patient');
            window.location.href = '/patient/login';
        }

        // Initialize
        loadServices();
        loadAppointments();
    </script>
</body>
</html>