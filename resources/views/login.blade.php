<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - AdaReach SMS Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center gradient-bg">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">AdaReach SMS Dashboard</h1>
            <p class="text-gray-500 mt-2">Sign in to access your dashboard</p>
        </div>

        <!-- Error Message -->
        <div id="error-message" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span id="error-text" class="text-red-800 text-sm"></span>
            </div>
        </div>

        <!-- Login Form -->
        <form id="login-form" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                    Username
                </label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all"
                    placeholder="Enter your username"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all"
                    placeholder="Enter your password"
                >
            </div>

            <button 
                type="submit" 
                id="login-btn"
                class="w-full bg-gradient-to-r from-purple-500 to-indigo-600 text-white py-3 rounded-lg font-semibold hover:from-purple-600 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl"
            >
                Sign In
            </button>
        </form>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>© {{ date('Y') }} Alina IT Solution</p>
        </div>
    </div>

    <script>
        const form = document.getElementById('login-form');
        const errorDiv = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');
        const loginBtn = document.getElementById('login-btn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Hide error
            errorDiv.classList.add('hidden');
            
            // Disable button
            loginBtn.disabled = true;
            loginBtn.textContent = 'Signing in...';
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            try {
                // Get fresh CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                
                const response = await fetch('{{ url(config("adarearch.dashboard.path", "sms-dashboard") . "/login") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.href = result.redirect;
                } else {
                    // Show error
                    let errorMessage = result.message || 'Invalid credentials';
                    
                    // Handle CSRF token mismatch specifically
                    if (response.status === 419) {
                        errorMessage = 'Session expired. Please refresh the page and try again.';
                    }
                    
                    errorText.textContent = errorMessage;
                    errorDiv.classList.remove('hidden');
                    
                    // Reset button
                    loginBtn.disabled = false;
                    loginBtn.textContent = 'Sign In';
                }
            } catch (error) {
                console.error('Login error:', error);
                errorText.textContent = 'An error occurred. Please try again.';
                errorDiv.classList.remove('hidden');
                
                // Reset button
                loginBtn.disabled = false;
                loginBtn.textContent = 'Sign In';
            }
        });
    </script>
</body>
</html>
