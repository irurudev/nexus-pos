import axios from 'axios';

// FE environment: 'deployment' (default) or 'production'
const FE_ENV = import.meta.env.VITE_FE_ENV || 'deployment';

// Rule:
// - if FE_ENV === 'production' => use VITE_API_PUBLIC
// - otherwise (deployment) => use VITE_API_LOCAL
// Fallback to VITE_API_URL for compatibility, then use current origin + /api
// (avoid hard-coded localhost in production builds)
const API_BASE_URL = (
  FE_ENV === 'production' ? import.meta.env.VITE_API_PUBLIC : import.meta.env.VITE_API_LOCAL
) || import.meta.env.VITE_API_URL || `${typeof window !== 'undefined' ? window.location.origin : 'http://localhost:8303'}/api`;

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
