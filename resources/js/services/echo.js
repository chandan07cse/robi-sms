import Echo from 'laravel-echo';
import io from 'socket.io-client';

window.io = io;

let echoInstance = null;

export function initializeEcho() {
  if (echoInstance) {
    return echoInstance;
  }

  const config = window.DASHBOARD_CONFIG;

  echoInstance = new Echo({
    broadcaster: 'socket.io',
    host: `${config.wsUrl}:${config.wsPort}`,
    transports: ['websocket', 'polling'],
    auth: {
      headers: {
        'X-CSRF-TOKEN': config.csrfToken,
      }
    }
  });

  return echoInstance;
}

export function disconnectEcho() {
  if (echoInstance) {
    echoInstance.disconnect();
    echoInstance = null;
  }
}

export function getEcho() {
  return echoInstance || initializeEcho();
}
