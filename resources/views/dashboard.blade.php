<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SMS Dashboard - AdaReach</title>
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>📱</text></svg>">
    
    <!-- Load Tailwind CSS -->
    @php
        $cssFiles = glob(public_path('vendor/adarearch/assets/*.css'));
        $cssFile = !empty($cssFiles) ? basename($cssFiles[0]) : 'main.css';
    @endphp
    <link rel="stylesheet" href="{{ asset('vendor/adarearch/assets/' . $cssFile) }}">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            overflow: hidden;
        }

        #app {
            width: 100vw;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .loading {
            text-align: center;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            color: white;
            font-size: 18px;
            font-weight: 500;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            margin: 20px;
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="loading">
            <div class="loading-spinner"></div>
            <div class="loading-text">Loading SMS Dashboard...</div>
        </div>
    </div>

    <script>
        window.DASHBOARD_CONFIG = {
            apiUrl: '{{ url(config("adarearch.dashboard.path", "sms-dashboard") . "/api") }}',
            dashboardPath: '{{ config("adarearch.dashboard.path", "sms-dashboard") }}',
            path: '{{ config("adarearch.dashboard.path", "sms-dashboard") }}',
            csrfToken: '{{ csrf_token() }}',
            wsUrl: '{{ config("app.url") }}',
            wsPort: {{ config('adarearch.dashboard.socket_io_port', 3000) }},
            author: 'Md. Ainul Moin Noor',
        };
    </script>
    <script type="module" src="{{ asset('vendor/adarearch/dashboard.js') }}"></script>
</body>
</html>
