import apiClient from '../api/client';

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
}

export interface LoginResponse {
  success: boolean;
  message: string;
  data: {
    user: User;
    token: string;
  };
}

export interface Kategori {
  id_kategori: number;
  nama_kategori: string;
}

export interface Barang {
  kode_barang: string;
  kategori_id: number;
  nama: string;
  harga_beli: number;
  harga_jual: number;
  stok: number;
  kategori?: Kategori;
}

export interface Pelanggan {
  id_pelanggan: string;
  nama: string;
  domisili: string;
  jenis_kelamin: 'PRIA' | 'WANITA';
  poin?: number;
}

export interface ItemPenjualan {
  kode_barang: string;
  qty: number;
  harga_satuan: number;
  jumlah: number;
  barang?: Barang;
}

export interface Penjualan {
  id_nota: string;
  tgl: string;
  kode_pelanggan: string;
  user_id: number;
  subtotal: number;
  diskon: number;
  pajak: number;
  total_akhir: number;
  itemPenjualans: ItemPenjualan[];
  pelanggan?: Pelanggan;
  user?: User;
}

export interface CreatePenjualanData {
  id_nota?: string;
  tgl?: string;
  id_pelanggan?: string;
  kode_pelanggan?: string;
  diskon?: number;
  pajak?: number;
  items: {
    kode_barang?: string;
    id_barang?: string;
    qty?: number;
    jumlah: number;
    harga_satuan?: number;
  }[];
}

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
}

export interface PaginatedResponse<T> {
  success: boolean;
  message: string;
  data: T[];
  pagination?: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from?: number;
    to?: number;
  };
  meta?: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
}

export const authAPI = {
  login: async (credentials: LoginCredentials): Promise<LoginResponse> => {
    const response = await apiClient.post('/login', credentials);
    return response.data;
  },

  logout: async () => {
    const response = await apiClient.post('/logout');
    return response.data;
  },

  me: async () => {
    const response = await apiClient.get('/me');
    return response.data;
  },
};

export const kategoriAPI = {
  getAll: async (params?: { per_page?: number; page?: number; search?: string }): Promise<PaginatedResponse<Kategori>> => {
    const defaultParams = params ?? { per_page: 1000, page: 1 };
    const response = await apiClient.get('/kategoris', { params: defaultParams });
    return response.data;
  },

  getById: async (id: number): Promise<ApiResponse<Kategori>> => {
    const response = await apiClient.get(`/kategoris/${id}`);
    return response.data;
  },

  create: async (data: { nama_kategori: string }): Promise<ApiResponse<Kategori>> => {
    const response = await apiClient.post('/kategoris', data);
    return response.data;
  },

  update: async (id: number, data: { nama_kategori: string }): Promise<ApiResponse<Kategori>> => {
    const response = await apiClient.put(`/kategoris/${id}`, data);
    return response.data;
  },

  delete: async (id: number): Promise<ApiResponse<null>> => {
    const response = await apiClient.delete(`/kategoris/${id}`);
    return response.data;
  },
};

export const barangAPI = {
  getAll: async (params?: { per_page?: number; page?: number; search?: string; kategori_id?: number }): Promise<PaginatedResponse<Barang>> => {
    const response = await apiClient.get('/barangs', { params });
    return response.data;
  },

  getById: async (kode: string): Promise<ApiResponse<Barang>> => {
    const response = await apiClient.get(`/barangs/${kode}`);
    return response.data;
  },

  create: async (data: {
    kode_barang?: string;
    kategori_id: number;
    nama: string;
    harga_beli: number;
    harga_jual: number;
    stok: number;
  }): Promise<ApiResponse<Barang>> => {
    const response = await apiClient.post('/barangs', data);
    return response.data;
  },

  update: async (kode: string, data: {
    kategori_id?: number;
    nama?: string;
    harga_beli?: number;
    harga_jual?: number;
    stok?: number;
  }): Promise<ApiResponse<Barang>> => {
    const response = await apiClient.put(`/barangs/${kode}`, data);
    return response.data;
  },

  delete: async (kode: string): Promise<ApiResponse<null>> => {
    const response = await apiClient.delete(`/barangs/${kode}`);
    return response.data;
  },
};

export const pelangganAPI = {
  getAll: async (params?: { per_page?: number; page?: number; search?: string }): Promise<PaginatedResponse<Pelanggan>> => {
    const defaultParams = params ?? { per_page: 1000, page: 1 };
    const response = await apiClient.get('/pelanggans', { params: defaultParams });
    return response.data;
  },

  getById: async (id: string): Promise<ApiResponse<Pelanggan>> => {
    const response = await apiClient.get(`/pelanggans/${id}`);
    return response.data;
  },

  create: async (data: {
    id_pelanggan?: string;
    nama: string;
    domisili?: string;
    jenis_kelamin: 'PRIA' | 'WANITA';
  }): Promise<ApiResponse<Pelanggan>> => {
    const response = await apiClient.post('/pelanggans', data);
    return response.data;
  },

  update: async (id: string, data: {
    nama?: string;
    domisili?: string;
    jenis_kelamin?: 'PRIA' | 'WANITA';
  }): Promise<ApiResponse<Pelanggan>> => {
    const response = await apiClient.put(`/pelanggans/${id}`, data);
    return response.data;
  },

  delete: async (id: string): Promise<ApiResponse<null>> => {
    const response = await apiClient.delete(`/pelanggans/${id}`);
    return response.data;
  },
};

export const usersAPI = {
  getAll: async (params?: { per_page?: number; page?: number; search?: string }) => {
    const defaultParams = params ?? { per_page: 10, page: 1 };
    const response = await apiClient.get('/users', { params: defaultParams });
    return response.data as PaginatedResponse<any>;
  },

  getById: async (id: number) => {
    const response = await apiClient.get(`/users/${id}`);
    return response.data as ApiResponse<any>;
  },

  create: async (data: { name: string; username: string; email: string; password: string; role: string; is_active?: boolean }) => {
    const response = await apiClient.post('/users', data);
    return response.data as ApiResponse<any>;
  },

  update: async (id: number, data: { name: string; username: string; email: string; password?: string | null; role: string; is_active?: boolean }) => {
    const response = await apiClient.put(`/users/${id}`, data);
    return response.data as ApiResponse<any>;
  },

  delete: async (id: number) => {
    const response = await apiClient.delete(`/users/${id}`);
    return response.data as ApiResponse<null>;
  },
};


export const penjualanAPI = {
  getAll: async (params?: { per_page?: number; page?: number; start_date?: string; end_date?: string; search?: string }): Promise<PaginatedResponse<Penjualan>> => {
    const response = await apiClient.get('/penjualans', { params });
    return response.data;
  },

  getById: async (id: string): Promise<ApiResponse<Penjualan>> => {
    const response = await apiClient.get(`/penjualans/${id}`);
    return response.data;
  },

  create: async (data: CreatePenjualanData): Promise<ApiResponse<Penjualan>> => {
    const response = await apiClient.post('/penjualans', data);
    return response.data;
  },

  getSummary: async (params?: { start_date?: string; end_date?: string }): Promise<ApiResponse<{
    total_penjualan: number;
    total_pendapatan: number;
    total_item: number;
    rata_rata_transaksi: number;
    top_kategori: Array<{
      kategori: string;
      total_penjualan: number;
      persentase: number;
    }>;
    kasir_performance: Array<{
      kasir: string;
      total_transaksi: number;
      total_pendapatan: number;
    }>;
  }>> => {
    const response = await apiClient.get('/penjualans/summary', { params });
    return response.data;
  },
};

export const analyticsAPI = {
  getSummary: async (): Promise<ApiResponse<{
    periode: { start_date: string; end_date: string };
    total_penjualan: number;
    total_diskon: number;
    total_pajak: number;
    total_laba: number;
    jumlah_transaksi: number;
    rata_rata_transaksi: number;
  }>> => {
    const response = await apiClient.get('/analytics/summary');
    return response.data;
  },

  getTopKategori: async (): Promise<ApiResponse<{ periode: { start_date: string; end_date: string }; items: Array<{
    id: number;
    nama_kategori: string;
    total_qty: number;
    total_penjualan: number;
  }>}>> => {
    const response = await apiClient.get('/analytics/top-kategori');
    return response.data;
  },

  getKasirPerformance: async (): Promise<ApiResponse<{ year: number; items: Array<{
    id: number;
    name: string;
    username: string;
    bulan: number;
    total_penjualan: number;
    jumlah_transaksi: number;
  }>}>> => {
    const response = await apiClient.get('/analytics/kasir-performance');
    return response.data;
  },
};

export const auditAPI = {
  getAll: async (params?: { per_page?: number; page?: number; user_id?: number; auditable_type?: string }): Promise<PaginatedResponse<any>> => {
    const response = await apiClient.get('/audit-logs', { params });
    return response.data;
  },
};