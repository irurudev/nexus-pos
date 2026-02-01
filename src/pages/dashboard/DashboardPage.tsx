import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Box,
  SimpleGrid,
  Card,
  VStack,
  Heading,
  Text,
  Spinner,
  Center,
  Icon,
  createToaster,
  Table,
} from '@chakra-ui/react';
import { FiTrendingUp, FiShoppingCart, FiPackage, FiDollarSign, FiUserPlus } from 'react-icons/fi';
import { analyticsAPI, penjualanAPI } from '../../services';

const toaster = createToaster({
  placement: 'top-end',
  pauseOnPageIdle: true,
});

interface SalesData {
  total_penjualan: number;
  total_diskon: number;
  total_pajak: number;
  total_laba: number;
  jumlah_transaksi: number;
  rata_rata_transaksi: number;
}

export default function DashboardPage() {
  const navigate = useNavigate();
  const [salesData, setSalesData] = useState<SalesData | null>(null);
  const [kategoriData, setKategoriData] = useState<Array<{ id: number; nama_kategori: string; total_qty: number; total_penjualan: number }>>([]);
  const [kasirPerformance, setKasirPerformance] = useState<Array<{ id: number; name: string; username: string; total_penjualan: number; jumlah_transaksi: number }>>([]);
  const [topPelanggan, setTopPelanggan] = useState<Array<{ kode_pelanggan: string; nama?: string; total_penjualan: number; jumlah_transaksi: number }>>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      setIsLoading(true);
      const response = await analyticsAPI.getSummary();
      setSalesData(response.data);

      // fetch top kategori and kasir performance
      try {
        const topKategoriRes = await analyticsAPI.getTopKategori();
        if (topKategoriRes.data && Array.isArray(topKategoriRes.data.items)) {
          const top = topKategoriRes.data.items
            .slice()
            .sort((a, b) => (b.total_penjualan || 0) - (a.total_penjualan || 0))
            .slice(0, 5);
          setKategoriData(top);
        }
      } catch (e) {
        console.warn('Failed to load top kategori', e);
      }

      try {
        const kp = await analyticsAPI.getKasirPerformance();
        if (kp.data && Array.isArray(kp.data.items)) {
          // aggregate by id/name
          const agg = kp.data.items.reduce<Record<number, { id: number; name: string; username: string; total_penjualan: number; jumlah_transaksi: number }>>((acc, cur) => {
            if (!acc[cur.id]) {
              acc[cur.id] = { id: cur.id, name: cur.name, username: cur.username, total_penjualan: cur.total_penjualan || 0, jumlah_transaksi: cur.jumlah_transaksi || 0 };
            } else {
              acc[cur.id].total_penjualan += cur.total_penjualan || 0;
              acc[cur.id].jumlah_transaksi += cur.jumlah_transaksi || 0;
            }
            return acc;
          }, {});
          const list = Object.values(agg)
            .slice()
            .sort((a, b) => (b.total_penjualan || 0) - (a.total_penjualan || 0))
            .slice(0, 5);
          setKasirPerformance(list);
        }
      } catch (e) {
        console.warn('Failed to load kasir performance', e);
      }

      // Fetch penjualans to compute Top Pelanggan (aggregate by kode_pelanggan)
      try {
        const perPage = 1000;
        const startDate = response.data?.periode?.start_date;
        const endDate = response.data?.periode?.end_date;
        const penjualansRes = await penjualanAPI.getAll({ per_page: perPage, start_date: startDate, end_date: endDate });
        if (penjualansRes.data && Array.isArray(penjualansRes.data)) {
          const agg = penjualansRes.data.reduce<Record<string, { kode_pelanggan: string; nama?: string; total_penjualan: number; jumlah_transaksi: number }>>((acc, cur) => {
            const key = cur.kode_pelanggan || String(cur.id_nota);
            const nama = cur.pelanggan?.nama || cur.kode_pelanggan || 'Umum';
            const raw = Number(cur.total_akhir ?? 0);
            const amount = Number.isFinite(raw) ? raw : 0;
            if (!acc[key]) {
              acc[key] = { kode_pelanggan: key, nama, total_penjualan: amount, jumlah_transaksi: 1 };
            } else {
              acc[key].total_penjualan += amount;
              acc[key].jumlah_transaksi += 1;
            }
            return acc;
          }, {});

          const arr = Object.values(agg).sort((a, b) => b.total_penjualan - a.total_penjualan).slice(0, 5);
          setTopPelanggan(arr);
        }
      } catch (e) {
        console.warn('Failed to load penjualans for top pelanggan', e);
      }

    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toaster.error({
        title: 'Error',
        description: err.response?.data?.message || 'Gagal memuat data dashboard',
      });
    } finally {
      setIsLoading(false);
    }
  };

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(value);
  };

  if (isLoading) {
    return (
      <Center py={20}>
        <Spinner size="lg" color="blue.500" />
      </Center>
    );
  }

  return (
    <Box>
      <VStack align="stretch" gap={8}>
        {/* Header */}
        <Box>
          <Heading size="lg" mb={2}>Dashboard</Heading>
          <Text color="gray.600">Ringkasan penjualan hari ini</Text>
        </Box>

        {/* Stats Cards */}
        {salesData && (
          <SimpleGrid columns={{ base: 1, md: 2, lg: 4 }} gap={6}>
            <Card.Root borderTop="4px" borderTopColor="blue.500">
              <Card.Body>
                <VStack align="start" gap={2}>
                  <Icon fontSize="3xl" color="blue.500">
                    <FiDollarSign />
                  </Icon>
                  <Text fontSize="xs" color="gray.600" fontWeight="medium">
                    Total Penjualan
                  </Text>
                  <Heading size="lg">
                    {formatCurrency(salesData.total_penjualan)}
                  </Heading>
                  <Text fontSize="xs" color="gray.500">
                    Dari semua transaksi
                  </Text>
                </VStack>
              </Card.Body>
            </Card.Root>

            <Card.Root borderTop="4px" borderTopColor="orange.500">
              <Card.Body>
                <VStack align="start" gap={2}>
                  <Icon fontSize="3xl" color="orange.500">
                    <FiShoppingCart />
                  </Icon>
                  <Text fontSize="xs" color="gray.600" fontWeight="medium">
                    Total Transaksi
                  </Text>
                  <Heading size="lg">{salesData.jumlah_transaksi}</Heading>
                  <Text fontSize="xs" color="gray.500">
                    Jumlah transaksi
                  </Text>
                </VStack>
              </Card.Body>
            </Card.Root>

            <Card.Root borderTop="4px" borderTopColor="green.500">
              <Card.Body>
                <VStack align="start" gap={2}>
                  <Icon fontSize="3xl" color="green.500">
                    <FiPackage />
                  </Icon>
                  <Text fontSize="xs" color="gray.600" fontWeight="medium">
                    Total Laba
                  </Text>
                  <Heading size="lg">{formatCurrency(salesData.total_laba)}</Heading>
                  <Text fontSize="xs" color="gray.500">
                    Laba kotor
                  </Text>
                </VStack>
              </Card.Body>
            </Card.Root>

            <Card.Root borderTop="4px" borderTopColor="purple.500">
              <Card.Body>
                <VStack align="start" gap={2}>
                  <Icon fontSize="3xl" color="purple.500">
                    <FiTrendingUp />
                  </Icon>
                  <Text fontSize="xs" color="gray.600" fontWeight="medium">
                    Rata-rata Transaksi
                  </Text>
                  <Heading size="lg">
                    {formatCurrency(salesData.rata_rata_transaksi)}
                  </Heading>
                  <Text fontSize="xs" color="gray.500">
                    Per transaksi
                  </Text>
                </VStack>
              </Card.Body>
            </Card.Root>
          </SimpleGrid>
        )}

        {/* Quick Actions */}
        <Card.Root>
          <Card.Body>
            <Heading size="md" mb={4}>Quick Actions</Heading>
            <SimpleGrid columns={{ base: 1, md: 3 }} gap={4}>
              <Box
                p={4}
                bg="blue.50"
                rounded="md"
                cursor="pointer"
                role="button"
                tabIndex={0}
                onClick={() => navigate('/penjualan')}
                onKeyDown={(e) => (e.key === 'Enter' || e.key === ' ') && navigate('/penjualan')}
                transition="all 0.2s"
                _hover={{ bg: 'blue.100', transform: 'translateY(-2px)' }}
                aria-label="Buat Penjualan"
              >
                <Icon fontSize="2xl" color="blue.500" mb={2}>
                  <FiShoppingCart />
                </Icon>
                <Text fontWeight="semibold">Buat Penjualan</Text>
                <Text fontSize="xs" color="gray.600">
                  Transaksi penjualan baru
                </Text>
              </Box>

              <Box
                p={4}
                bg="green.50"
                rounded="md"
                cursor="pointer"
                role="button"
                tabIndex={0}
                onClick={() => navigate('/barang')}
                onKeyDown={(e) => (e.key === 'Enter' || e.key === ' ') && navigate('/barang')}
                transition="all 0.2s"
                _hover={{ bg: 'green.100', transform: 'translateY(-2px)' }}
                aria-label="Tambah Barang"
              >
                <Icon fontSize="2xl" color="green.500" mb={2}>
                  <FiPackage />
                </Icon>
                <Text fontWeight="semibold">Tambah Barang</Text>
                <Text fontSize="xs" color="gray.600">Masukkan produk baru ke katalog</Text>
              </Box>

              <Box
                p={4}
                bg="yellow.50"
                rounded="md"
                cursor="pointer"
                role="button"
                tabIndex={0}
                onClick={() => navigate('/pelanggan')}
                onKeyDown={(e) => (e.key === 'Enter' || e.key === ' ') && navigate('/pelanggan')}
                transition="all 0.2s"
                _hover={{ bg: 'yellow.100', transform: 'translateY(-2px)' }}
                aria-label="Tambah Pelanggan"
              >
                <Icon fontSize="2xl" color="yellow.600" mb={2}>
                  <FiUserPlus />
                </Icon>
                <Text fontWeight="semibold">Tambah Pelanggan</Text>
                <Text fontSize="xs" color="gray.600">Tambahkan pelanggan baru</Text>
              </Box>
            </SimpleGrid>
          </Card.Body>
        </Card.Root>

        {/* Analytics Cards */}
        <SimpleGrid columns={{ base: 1, md: 3 }} gap={6}>
          {/* Top Kategori */}
          <Card.Root>
            <Card.Body>
              <Heading size="md" mb={4}>Top Kategori</Heading>
              {kategoriData.length === 0 ? (
                <Center py={8}><Text color="gray.500">Belum ada data kategori</Text></Center>
              ) : (
                <Box overflowX="auto">
                  <Box minW={{ base: '600px', md: 'auto' }}>
                    <Table.Root>
                      <Table.Header>
                        <Table.Row bg="gray.50">
                          <Table.ColumnHeader>Kategori</Table.ColumnHeader>
                          <Table.ColumnHeader textAlign="end">Qty Terjual</Table.ColumnHeader>
                          <Table.ColumnHeader textAlign="end">Total Penjualan</Table.ColumnHeader>
                        </Table.Row>
                      </Table.Header>
                      <Table.Body>
                        {kategoriData.map((item) => (
                          <Table.Row key={item.id}>
                            <Table.Cell fontWeight="semibold">{item.nama_kategori}</Table.Cell>
                            <Table.Cell textAlign="end" fontWeight="semibold">{item.total_qty}</Table.Cell>
                            <Table.Cell textAlign="end">{formatCurrency(item.total_penjualan)}</Table.Cell>
                          </Table.Row>
                        ))}
                      </Table.Body>
                    </Table.Root>
                  </Box>
                </Box>
              )}
            </Card.Body>
          </Card.Root>

          {/* Kasir Performance */}
          <Card.Root>
            <Card.Body>
              <Heading size="md" mb={4}>Kasir Performance (YTD)</Heading>
              {kasirPerformance.length === 0 ? (
                <Center py={8}><Text color="gray.500">Belum ada data kasir</Text></Center>
              ) : (
                <Box overflowX="auto">
                  <Box minW={{ base: '600px', md: 'auto' }}>
                    <Table.Root>
                      <Table.Header>
                        <Table.Row bg="gray.50">
                          <Table.ColumnHeader>Nama Kasir</Table.ColumnHeader>
                          <Table.ColumnHeader textAlign="end">Transaksi</Table.ColumnHeader>
                          <Table.ColumnHeader textAlign="end">Total Penjualan</Table.ColumnHeader>
                        </Table.Row>
                      </Table.Header>
                      <Table.Body>
                        {kasirPerformance.map((k) => (
                          <Table.Row key={k.id}>
                            <Table.Cell fontWeight="semibold">{k.name} ({k.username})</Table.Cell>
                            <Table.Cell textAlign="end">{k.jumlah_transaksi}</Table.Cell>
                            <Table.Cell textAlign="end">{formatCurrency(k.total_penjualan)}</Table.Cell>
                          </Table.Row>
                        ))}
                      </Table.Body>
                    </Table.Root>
                  </Box>
                </Box>
              )}
            </Card.Body>
          </Card.Root>

          {/* Top Pelanggan */}
          <Card.Root>
            <Card.Body>
              <Heading size="md" mb={4}>Top Pelanggan (By Total Pembelian)</Heading>
              {topPelanggan.length === 0 ? (
                <Center py={8}><Text color="gray.500">Belum ada data pelanggan</Text></Center>
              ) : (
                <Box overflowX="auto">
                  <Box minW={{ base: '600px', md: 'auto' }}>
                    <Table.Root>
                      <Table.Header>
                        <Table.Row bg="gray.50">
                          <Table.ColumnHeader>Nama Pelanggan</Table.ColumnHeader>
                          <Table.ColumnHeader textAlign="end">Transaksi</Table.ColumnHeader>
                          <Table.ColumnHeader textAlign="end">Total Pembelian</Table.ColumnHeader>
                        </Table.Row>
                      </Table.Header>
                      <Table.Body>
                        {topPelanggan.map((p) => (
                          <Table.Row key={p.kode_pelanggan}>
                            <Table.Cell fontWeight="semibold">{p.nama}</Table.Cell>
                            <Table.Cell textAlign="end">{p.jumlah_transaksi}</Table.Cell>
                            <Table.Cell textAlign="end">{formatCurrency(p.total_penjualan)}</Table.Cell>
                          </Table.Row>
                        ))}
                      </Table.Body>
                    </Table.Root>
                  </Box>
                </Box>
              )}
            </Card.Body>
          </Card.Root>
        </SimpleGrid>
      </VStack>
    </Box>
  );
}
