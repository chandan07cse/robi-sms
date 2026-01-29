#!/bin/bash

# AdaReach SMS Package - Installation Script
# Developed by: Md. Ainul Moin Noor

echo "🚀 Installing AdaReach SMS Dashboard..."
echo ""

# Install NPM dependencies for dashboard
echo "📦 Installing dashboard dependencies..."
npm install

# Build dashboard assets
echo "🔨 Building dashboard assets..."
npm run build

# Install Socket.IO server dependencies
echo "📡 Installing Socket.IO server dependencies..."
npm install --prefix . express socket.io redis

echo ""
echo "✅ Installation complete!"
echo ""
echo "📝 Next steps:"
echo "1. Configure your .env file with API credentials"
echo "2. Start Redis: redis-server"
echo "3. Start Socket.IO Server: ./start-socket-server.sh"
echo "4. Start Dashboard: php artisan adarearch:serve --port=8090"
echo "5. Visit: http://localhost:8090/sms-dashboard"
echo ""
echo "📚 Documentation:"
echo "- Quick Start: INSTALLATION.md"
echo "- Socket.IO Setup: SOCKET_IO_SETUP.md"
echo "- Full README: README.md"
echo ""
echo "👨‍💻 Developed by: Md. Ainul Moin Noor"
echo ""
