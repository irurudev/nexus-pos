import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from '../context/AuthContext';
import LoginPage from '../pages/auth/LoginPage';
import DashboardPage from '../pages/dashboard/DashboardPage';
import KategoriPage from '../pages/kategoris/KategoriPage';
import BarangPage from '../pages/barangs/BarangPage';
import PelangganPage from '../pages/pelanggans/PelangganPage';
import PenjualanPage from '../pages/penjualans/PenjualanPage';
import DashboardLayout from '../layouts/DashboardLayout';
import AuditLogsPage from '../pages/audit-logs/AuditLogsPage';

// Protected Route Component
function ProtectedRoute({ children }: { children: React.ReactNode }) {
  // Defensive: use try/catch in case context is not yet ready (HMR / startup)
  let isAuthenticated = false;
  let isLoading = true;
  try {
    const auth = useAuth();
    isAuthenticated = auth.isAuthenticated;
    isLoading = auth.isLoading;
  } catch (e) {
    // context not available yet -> show loading
    isLoading = true;
  }

  if (isLoading) {
    return <div>Loading...</div>;
  }

  return isAuthenticated ? <>{children}</> : <Navigate to="/login" />;
}

// Public Route Component (redirect if authenticated)
function PublicRoute({ children }: { children: React.ReactNode }) {
  let isAuthenticated = false;
  let isLoading = true;
  try {
    const auth = useAuth();
    isAuthenticated = auth.isAuthenticated;
    isLoading = auth.isLoading;
  } catch (e) {
    isLoading = true;
  }

  if (isLoading) {
    return <div>Loading...</div>;
  }

  return !isAuthenticated ? <>{children}</> : <Navigate to="/dashboard" />;
}

export function AppRoutes() {
  return (
    <Routes>
      {/* Public Routes */}
      <Route
        path="/login"
        element={
          <PublicRoute>
            <LoginPage />
          </PublicRoute>
        }
      />

      {/* Protected Routes */}
      <Route
        path="/dashboard"
        element={
          <ProtectedRoute>
            <DashboardLayout>
              <DashboardPage />
            </DashboardLayout>
          </ProtectedRoute>
        }
      />

      <Route
        path="/kategori"
        element={
          <ProtectedRoute>
            <DashboardLayout>
              <KategoriPage />
            </DashboardLayout>
          </ProtectedRoute>
        }
      />

      <Route
        path="/barang"
        element={
          <ProtectedRoute>
            <DashboardLayout>
              <BarangPage />
            </DashboardLayout>
          </ProtectedRoute>
        }
      />

      <Route
        path="/pelanggan"
        element={
          <ProtectedRoute>
            <DashboardLayout>
              <PelangganPage />
            </DashboardLayout>
          </ProtectedRoute>
        }
      />

      <Route
        path="/penjualan"
        element={
          <ProtectedRoute>
            <DashboardLayout>
              <PenjualanPage />
            </DashboardLayout>
          </ProtectedRoute>
        }
      />

      <Route
        path="/audit-logs"
        element={
          <ProtectedRoute>
            <DashboardLayout>
              <AuditLogsPage />
            </DashboardLayout>
          </ProtectedRoute>
        }
      />

      {/* Default redirect */}
      <Route path="/" element={<Navigate to="/dashboard" />} />
      <Route path="*" element={<Navigate to="/dashboard" />} />
    </Routes>
  );
}

export default function RoutesWrapper() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <AppRoutes />
      </AuthProvider>
    </BrowserRouter>
  );
}
