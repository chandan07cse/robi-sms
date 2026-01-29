#!/bin/bash

# AdaReach SMS Socket.IO Server Starter
# Developed by: Md. Ainul Moin Noor

echo "🚀 Starting Socket.IO Server for AdaReach SMS Dashboard..."
echo ""

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js first."
    exit 1
fi

# Install dependencies if node_modules doesn't exist
if [ ! -d "node_modules_socket" ]; then
    echo "📦 Installing Socket.IO server dependencies..."
    npm install --prefix . --package-lock-only=false express socket.io redis
fi

# Load environment variables
if [ -f ".env" ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

# Set default port if not set
export SOCKET_IO_SERVER_PORT=${SOCKET_IO_SERVER_PORT:-3000}

echo "📡 Starting Socket.IO server on port ${SOCKET_IO_SERVER_PORT}..."
echo ""

# Start the server
node socket-server.js
