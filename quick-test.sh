#!/bin/bash

# AdaReach SMS Dashboard - Quick Test Runner
# Developed by: Md. Ainul Moin Noor

set -e

PACKAGE_DIR="/home/noor/codes/robisms/robi-sms-package/adarearch-laravel"
TEST_APP_DIR="/home/noor/codes/robisms/sms-test-app"

echo "🚀 AdaReach SMS Dashboard - Quick Test"
echo "======================================="
echo ""

# Check if test app exists
if [ -d "$TEST_APP_DIR" ]; then
    echo "✅ Test app already exists at: $TEST_APP_DIR"
    echo ""
else
    echo "📦 Creating test Laravel app..."
    cd /home/noor/codes/robisms
    composer create-project laravel/laravel sms-test-app --no-interaction
    cd sms-test-app
    
    echo ""
    echo "🔗 Linking package locally..."
    
    # Add repository to composer.json
    composer config repositories.local '{"type": "path", "url": "../robi-sms-package/adarearch-laravel"}'
    
    # Install package
    composer require chandan07cse/robi-sms:@dev --no-interaction
    
    echo ""
    echo "📝 Publishing package assets..."
    php artisan vendor:publish --tag=adarearch-config --force
    php artisan vendor:publish --tag=adarearch-assets --force
    
    echo ""
    echo "⚙️  Configuring environment..."
    
    # Backup original .env
    cp .env .env.backup
    
    # Add required config to .env
    cat >> .env << 'EOF'

# AdaReach SMS Configuration
ADAREARCH_USERNAME=your_api_username
ADAREARCH_PASSWORD=your_api_password
ADAREARCH_DEFAULT_SENDER=TEST

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Broadcasting
BROADCAST_DRIVER=redis

# Socket.IO Server
SOCKET_IO_SERVER_PORT=3000

# Dashboard
ADAREARCH_DASHBOARD_PORT=8090
EOF

    echo ""
    echo "✅ Test app created and configured!"
fi

echo ""
echo "🔧 Pre-flight Checks"
echo "===================="

# Check Redis
if redis-cli ping > /dev/null 2>&1; then
    echo "✅ Redis is running"
else
    echo "❌ Redis is NOT running!"
    echo "   Start it with: redis-server"
    echo ""
    read -p "Do you want to start Redis now? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        redis-server --daemonize yes
        echo "✅ Redis started"
    else
        echo "⚠️  Please start Redis manually before continuing"
        exit 1
    fi
fi

# Check Node.js version
NODE_VERSION=$(node -v)
echo "✅ Node.js: $NODE_VERSION"

# Check if Socket.IO server is running
if curl -s http://localhost:3000/health > /dev/null 2>&1; then
    echo "✅ Socket.IO server is running"
else
    echo "⚠️  Socket.IO server is NOT running"
    echo ""
    read -p "Do you want to start Socket.IO server? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "🚀 Starting Socket.IO server..."
        cd "$PACKAGE_DIR"
        nohup ./start-socket-server.sh > /tmp/socket-io.log 2>&1 &
        SOCKET_PID=$!
        echo "   PID: $SOCKET_PID"
        
        # Wait for server to start
        sleep 3
        
        if curl -s http://localhost:3000/health > /dev/null 2>&1; then
            echo "✅ Socket.IO server started successfully"
        else
            echo "❌ Failed to start Socket.IO server"
            echo "   Check logs: tail -f /tmp/socket-io.log"
            exit 1
        fi
    fi
fi

echo ""
echo "🎯 Starting Dashboard Server"
echo "============================"
cd "$TEST_APP_DIR"

echo ""
echo "📱 Dashboard will be available at: http://localhost:8090/sms-dashboard"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

php artisan adarearch:serve --port=8090
