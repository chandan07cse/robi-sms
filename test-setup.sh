#!/bin/bash

# Quick Test Setup Script
# Developed by: Md. Ainul Moin Noor

echo "🧪 Setting up AdaReach SMS Package for Testing..."
echo ""

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Error: Not in a Laravel project directory"
    echo "Please run this from your Laravel project root"
    exit 1
fi

# Install dependencies
echo "📦 Installing package dependencies..."
npm install

# Build assets
echo "🔨 Building dashboard assets..."
npm run build

# Install Socket.IO dependencies
echo "📡 Installing Socket.IO server dependencies..."
npm install --prefix . express socket.io redis

# Make scripts executable
chmod +x install.sh start-socket-server.sh

echo ""
echo "✅ Setup complete!"
echo ""
echo "📝 Quick Start Commands:"
echo ""
echo "Terminal 1 - Start Redis:"
echo "  redis-server"
echo ""
echo "Terminal 2 - Start Socket.IO:"
echo "  cd vendor/chandan07cse/robi-sms && ./start-socket-server.sh"
echo ""
echo "Terminal 3 - Start Dashboard:"
echo "  php artisan adarearch:serve --port=8090"
echo ""
echo "Terminal 4 - Your Laravel App (optional):"
echo "  php artisan serve"
echo ""
echo "🌐 Access Dashboard:"
echo "  http://localhost:8090/sms-dashboard"
echo ""
echo "📚 Read TESTING_GUIDE.md for detailed instructions"
echo ""
echo "👨‍💻 Developed by: Md. Ainul Moin Noor"
