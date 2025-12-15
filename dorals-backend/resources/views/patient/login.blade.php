<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Login - D-ORALS</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="bg-blue-600 w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">D-ORALS</h1>
            <p class="text-gray-600">Patient Login</p>
        </div>

        <!-- Alert Message -->
        <div id="alert" class="hidden mb-4 p-4 rounded-lg"></div>

        @if ($errors->any())
            <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <!-- Login Form -->
        <form id="loginForm" class="space-y-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Email Address</label>
                <input type="email" id="email" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="juan@example.com">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" id="password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter your password">
            </div>

            <button type="submit" id="loginBtn"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-200">
                Login
            </button>
        </form>

        <!-- Links -->
        <div class="mt-6 text-center space-y-2">
            <p class="text-gray-600">
                Don't have an account? 
                <a href="/patient/register" class="text-blue-600 hover:underline font-medium">Register here</a>
            </p>
            <p class="text-gray-600">
                <a href="/admin/login" class="text-blue-600 hover:underline font-medium">Admin Login</a>
            </p>
        </div>
    </div>

    <script>
        const API_BASE = 'http://127.0.0.1:8000/api';

        function showAlert(message, type) {
            const alert = document.getElementById('alert');
            alert.className = `mb-4 p-4 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
            alert.textContent = message;
            alert.classList.remove('hidden');

            if (type === 'success') {
                setTimeout(() => {
                    window.location.href = '/patient/dashboard';
                }, 1500);
            }
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');

            loginBtn.disabled = true;
            loginBtn.textContent = 'Logging in...';

            try {
                const response = await fetch(`${API_BASE}/patient/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email, password }),
                });

                const data = await response.json();

                if (response.ok) {
                    // Save token and patient data to localStorage
                    localStorage.setItem('patient_token', data.token);
                    localStorage.setItem('patient', JSON.stringify(data.patient));
                    
                    showAlert('Login successful! Redirecting...', 'success');
                } else {
                    showAlert(data.message || 'Login failed', 'error');
                    loginBtn.disabled = false;
                    loginBtn.textContent = 'Login';
                }
            } catch (error) {
                console.error('Login error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                loginBtn.disabled = false;
                loginBtn.textContent = 'Login';
            }
        });
    </script>
</body>
</html>