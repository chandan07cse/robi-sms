import express from 'express';
import { createServer } from 'http';
import { Server } from 'socket.io';
import { createClient } from 'redis';

// Configuration
const PORT = process.env.SOCKET_IO_SERVER_PORT || 3000;
const REDIS_HOST = process.env.REDIS_HOST || 'localhost';
const REDIS_PORT = process.env.REDIS_PORT || 6379;

// Create Express app and HTTP server
const app = express();
const server = createServer(app);

// Create Socket.IO instance with CORS
const io = new Server(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
    credentials: true
  },
  transports: ['websocket', 'polling']
});

// Create Redis subscriber
const subscriber = createClient({
  socket: {
    host: REDIS_HOST,
    port: REDIS_PORT
  }
});

subscriber.on('error', (err) => {
  console.error('Redis error:', err);
});

// Start the server
(async () => {
  // Connect to Redis
  await subscriber.connect();
  console.log('✅ Connected to Redis');

  // Subscribe to Laravel's broadcasting channel
  await subscriber.subscribe('sms-dashboard-channel', (message) => {
    try {
      const data = JSON.parse(message);
      
      // Laravel broadcasts in a specific format
      if (data.event) {
        io.emit(data.event, data.data);
        console.log(`📡 Broadcasted event: ${data.event}`);
      }
    } catch (error) {
      console.error('Error parsing message:', error);
    }
  });

// Socket.IO connection handler
io.on('connection', (socket) => {
  console.log('👤 Client connected:', socket.id);

  socket.on('disconnect', () => {
    console.log('👋 Client disconnected:', socket.id);
  });

  // Join specific channels
  socket.on('subscribe', (channel) => {
    socket.join(channel);
    console.log(`📢 Client ${socket.id} subscribed to ${channel}`);
  });

  socket.on('unsubscribe', (channel) => {
    socket.leave(channel);
    console.log(`🔕 Client ${socket.id} unsubscribed from ${channel}`);
  });
});

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({
    status: 'ok',
    connections: io.engine.clientsCount,
    timestamp: new Date().toISOString()
  });
});

// Start server
  server.listen(PORT, () => {
    console.log('🚀 Socket.IO Server Started');
    console.log(`📡 Listening on port: ${PORT}`);
    console.log(`🌐 Health check: http://localhost:${PORT}/health`);
    console.log(`📊 Dashboard: http://localhost:8090/sms-dashboard`);
    console.log('');
    console.log('👨‍💻 Developed by: Md. Ainul Moin Noor');
    console.log('');
  });
})();

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('🛑 SIGTERM received, shutting down gracefully...');
  server.close(() => {
    console.log('👋 Server closed');
    subscriber.quit();
    process.exit(0);
  });
});
