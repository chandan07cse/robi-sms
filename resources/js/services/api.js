import axios from 'axios';

const api = axios.create({
  baseURL: window.DASHBOARD_CONFIG.apiUrl,
  headers: {
    'X-CSRF-TOKEN': window.DASHBOARD_CONFIG.csrfToken,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

export default api;

export const smsList = (params) => api.get('/sms', { params });
export const smsDetails = (id) => api.get(`/sms/${id}`);
export const smsRetry = (id) => api.post(`/sms/${id}/retry`);
export const getStats = (params) => api.get('/stats', { params });
export const getCredits = () => api.get('/credits');
export const getOverview = (params) => api.get('/overview', { params });
export const exportSms = (params) => api.get('/export', { params, responseType: 'blob' });
