import { useMemo } from 'react';
import { useAuth } from '../context/AuthContext';

type Resource = 'penjualan' | 'barang' | 'kategori' | 'pelanggan' | string;

export default function usePermissions() {
  const { user } = useAuth();
  const role = user?.role ?? 'guest';

  // Define permission matrix per role
  const perms = useMemo(() => {
    const isAdmin = role === 'admin';
    const isKasir = role === 'kasir' || role === 'kasir';

    return {
      canView: (resource: Resource) => {
        if (isAdmin) return true;
        if (isKasir) return ['penjualan', 'barang', 'kategori', 'pelanggan'].includes(resource);
        return false;
      },
      canCreate: (resource: Resource) => {
        if (isAdmin) return true;
        if (isKasir) return resource === 'penjualan';
        return false;
      },
      canEdit: (_resource: Resource) => {
        if (isAdmin) return true;
        if (isKasir) return false;
        return false;
      },
      canDelete: (_resource: Resource) => {
        if (isAdmin) return true;
        if (isKasir) return false;
        return false;
      },
      role,
    };
  }, [role]);

  return perms;
}
