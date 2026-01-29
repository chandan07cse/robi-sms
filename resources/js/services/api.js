import axios from 'axios';

const api = axios.create({
  baseURL: window.DASHBOARD_CONFIG.apiUrl,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

// Add request interceptor to include fresh CSRF token
api.interceptors.request.use(config => {
  // Get fresh CSRF token from meta tag or window config
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content 
    || window.DASHBOARD_CONFIG.csrfToken;
  
  if (csrfToken) {
    config.headers['X-CSRF-TOKEN'] = csrfToken;
  }
  
  return config;
}, error => {
  return Promise.reject(error);
});

// Add response interceptor to handle CSRF token mismatch
api.interceptors.response.use(
  response => response,
  async error => {
    // If CSRF token mismatch (419), try to refresh the page once
    if (error.response?.status === 419) {
      console.warn('CSRF token mismatch detected. Page may need refresh.');
      
      // Try to get fresh CSRF token
      try {
        const response = await fetch(window.location.href, {
          method: 'HEAD',
          credentials: 'same-origin'
        });
        
        // Update CSRF token from response headers if available
        const newToken = response.headers.get('X-CSRF-TOKEN');
        if (newToken) {
          document.querySelector('meta[name="csrf-token"]').content = newToken;
          window.DASHBOARD_CONFIG.csrfToken = newToken;
          
          // Retry the original request
          const originalRequest = error.config;
          originalRequest.headers['X-CSRF-TOKEN'] = newToken;
          return api(originalRequest);
        }
      } catch (refreshError) {
        console.error('Failed to refresh CSRF token:', refreshError);
      }
      
      // If token refresh fails, show user-friendly message
      error.message = 'Session expired. Please refresh the page and try again.';
    }
    
    return Promise.reject(error);
  }
);

export default api;

export const smsList = (params) => api.get('/sms', { params });
export const smsDetails = (id) => api.get(`/sms/${id}`);
export const smsRetry = (id) => api.post(`/sms/${id}/retry`);
export const getStats = (params) => api.get('/stats', { params });
export const getCredits = () => api.get('/credits');
export const getOverview = (params) => api.get('/overview', { params });
export const exportSms = (params) => api.get('/export', { params, responseType: 'blob' });
