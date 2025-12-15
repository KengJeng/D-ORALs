<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration - D-ORALS</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen py-8 px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-auto p-8">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="bg-blue-600 w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">D-ORALS</h1>
            <p class="text-gray-600">Patient Registration</p>
        </div>

        <!-- Alert Message -->
        <div id="alert" class="hidden mb-4 p-4 rounded-lg"></div>

        <!-- Registration Form -->
        <form id="registerForm" class="space-y-6">
            <!-- Personal Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">First Name *</label>
                    <input type="text" id="first_name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Juan">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Middle Name</label>
                    <input type="text" id="middle_name"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Dela">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Last Name *</label>
                    <input type="text" id="last_name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Cruz">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Sex *</label>
                    <select id="sex" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select...</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>

            <!-- Contact Information -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Contact Number *</label>
                <input type="tel" id="contact_no" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="09123456789">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Address *</label>
                <textarea id="address" required rows="2"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="123 Main St, Quezon City"></textarea>
            </div>

            <!-- Account Information -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Email Address *</label>
                <input type="email" id="email" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="juan@example.com">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Password *</label>
                    <input type="password" id="password" required minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Min. 8 characters">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Confirm Password *</label>
                    <input type="password" id="password_confirmation" required minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Re-enter password">
                </div>
            </div>

            <button type="submit" id="registerBtn"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-200">
                Register
            </button>
        </form>

        <!-- Links -->
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                Already have an account? 
                <a href="/patient/login" class="text-blue-600 hover:underline font-medium">Login here</a>
            </p>
        </div>
    </div>

    <script>
        const API_BASE = 'http://127.0.0.1:8000/api'; // Correct


        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            // Get trimmed values
            const first_name = document.getElementById('first_name').value.trim();
            const last_name = document.getElementById('last_name').value.trim();
            const sex = document.getElementById('sex').value;
            const contact_no = document.getElementById('contact_no').value.trim();
            const address = document.getElementById('address').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const password_confirmation = document.getElementById('password_confirmation').value;

            // Validation
            const errors = [];
            if (!first_name) errors.push('First name is required.');
            if (!last_name) errors.push('Last name is required.');
            if (!sex) errors.push('Sex is required.');
            if (!contact_no) errors.push('Contact number is required.');
            else if (!/^\d{11}$/.test(contact_no)) errors.push('Contact number must be exactly 11 digits.');
            if (!address) errors.push('Address is required.');
            if (!email) errors.push('Email is required.');
            if (!password) errors.push('Password is required.');
            if (!password_confirmation) errors.push('Confirm password is required.');
            if (password && password_confirmation && password !== password_confirmation) errors.push('Passwords do not match.');

            if (errors.length > 0) {
                showAlert(errors.join(' '), 'error');
                return;
            }

            const formData = {
                first_name: first_name,
                middle_name: document.getElementById('middle_name').value.trim(),
                last_name: last_name,
                sex: sex,
                contact_no: contact_no,
                address: address,
                email: email,
                password: password,
                password_confirmation: password_confirmation,
            };

            const registerBtn = document.getElementById('registerBtn');
            registerBtn.disabled = true;
            registerBtn.textContent = 'Registering...';

            try {
                const response = await fetch(`${API_BASE}/patient/register`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData),
                });

                const data = await response.json();

                if (response.ok) {
                    showAlert('Registration successful! Redirecting to login...', 'success');
                    localStorage.setItem('patient_token', data.token);
                    localStorage.setItem('patient', JSON.stringify(data.patient));
                    
                    setTimeout(() => {
                        window.location.href = '/patient/dashboard';
                    }, 1500);
                } else {
                    const errors = data.errors ? Object.values(data.errors).flat().join(', ') : data.message;
                    showAlert(errors || 'Registration failed. Please check your information.', 'error');
                    registerBtn.disabled = false;
                    registerBtn.textContent = 'Register';
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'error');
                registerBtn.disabled = false;
                registerBtn.textContent = 'Register';
            }
        });

        function showAlert(message, type) {
            const alert = document.getElementById('alert');
            alert.className = `mb-4 p-4 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
            alert.textContent = message;
            alert.classList.remove('hidden');
        }
    </script>
</body>
</html>