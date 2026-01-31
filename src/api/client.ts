import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8303/api';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor untuk menambahkan token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor untuk handle errors
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Remove persisted auth data
      localStorage.removeItem('token');
      localStorage.removeItem('user');

      // Notify the application so React context can clear in-memory state
      // This avoids UI showing stale authenticated state when token is invalid
      try {
        window.dispatchEvent(new CustomEvent('auth:unauthorized', { detail: { status: 401 } }));
      } catch (e) {
        // Older browsers may not support CustomEvent constructor
        const evt = document.createEvent('Event');
        evt.initEvent('auth:unauthorized', true, true);
        window.dispatchEvent(evt);
      }

      // fallback redirect to login
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default apiClient;
