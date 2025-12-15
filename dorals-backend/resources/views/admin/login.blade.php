<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - D-ORALS</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gradient-to-br from-gray-800 to-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="bg-gray-800 w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">D-ORALS</h1>
            <p class="text-gray-600">Admin Dashboard Access</p>
        </div>

        <!-- Alert Message -->
        <div id="alert" class="hidden mb-4 p-4 rounded-lg"></div>

        <!-- Login Form -->
        <form id="loginForm" class="space-y-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Email Address</label>
                <input type="email" id="email" required value="admin@dorals.com"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                    placeholder="admin@dorals.com">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" id="password" required value="password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                    placeholder="Enter your password">
            </div>

            <button type="submit" id="loginBtn"
                class="w-full bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 rounded-lg transition duration-200">
                Login to Dashboard
            </button>
        </form>

        <!-- Links -->
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                <a href="/patient/login" class="text-gray-800 hover:underline font-medium">Patient Login</a>
            </p>
        </div>

        <!-- Default Credentials Info -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-700 font-medium mb-1">Default Credentials:</p>
            <p class="text-sm text-gray-600">Email: admin@dorals.com</p>
            <p class="text-sm text-gray-600">Password: password</p>
        </div>
    </div>

    <script>
        const API_BASE = 'http://127.0.0.1:8000/api'; // Correct

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');

            loginBtn.disabled = true;
            loginBtn.textContent = 'Logging in...';

            try {
                const response = await fetch(`${API_BASE}/admin/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email, password }),
                });

                const data = await response.json();

                if (response.ok) {
                    showAlert('Login successful! Redirecting...', 'success');
                    localStorage.setItem('admin_token', data.token);
                    localStorage.setItem('admin', JSON.stringify(data.admin));
                    
                    setTimeout(() => {
                        window.location.href = '/admin/dashboard';
                    }, 1000);
                } else {
                    showAlert(data.message || 'Login failed. Please check your credentials.', 'error');
                    loginBtn.disabled = false;
                    loginBtn.textContent = 'Login to Dashboard';
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'error');
                loginBtn.disabled = false;
                loginBtn.textContent = 'Login to Dashboard';
            }
        });

        function showAlert(message, type) {
            const alert = document.getElementById('alert');
            alert.className = `mb-4 p-4 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
            alert.textContent = message;
            alert.classList.remove('hidden');

            if (type === 'success') {
                setTimeout(() => alert.classList.add('hidden'), 3000);
            }
        }
    </script>
</body>
</html>