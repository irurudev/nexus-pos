import { useEffect, useState } from 'react';
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
} from '@chakra-ui/react';
import { FiTrendingUp, FiShoppingCart, FiPackage, FiBarChart2 } from 'react-icons/fi';
import { analyticsAPI } from '../../services';

const toaster = createToaster({
  placement: 'top-end',
  pauseOnPageIdle: true,
});

interface SalesData {
  periode: { start_date: string; end_date: string };
  total_penjualan: number;
  total_diskon: number;
  total_pajak: number;
  total_laba: number;
  jumlah_transaksi: number;
  rata_rata_transaksi: number;
}


export default function AnalyticsPage() {
  const [salesData, setSalesData] = useState<SalesData | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    fetchAnalytics();
  }, []);

  const fetchAnalytics = async () => {
    try {
      setIsLoading(true);
      const response = await analyticsAPI.getSummary();
      // services return ApiResponse<...> (response.data is the payload)
      setSalesData(response.data || null);

    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toaster.error({
        title: 'Error',
        description: err.response?.data?.message || 'Gagal memuat analytics',
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
        <Box>
          <Heading size="lg" mb={2}>Analytics</Heading>
          <Text color="gray.600">Ringkasan dan analisis penjualan</Text>
        </Box>

        {salesData && (
          <SimpleGrid columns={{ base: 1, md: 2, lg: 4 }} gap={6}>
            <Card.Root borderTop="4px" borderTopColor="blue.500">
              <Card.Body>
                <VStack align="start" gap={2}>
                  <Icon fontSize="2xl" color="blue.500">
                    <FiTrendingUp />
                  </Icon>
                  <Text fontSize="xs" color="gray.600" fontWeight="medium">
                    Total Penjualan
                  </Text>
                  <Heading size="md">{formatCurrency(salesData.total_penjualan)}</Heading>
                  <Text fontSize="xs" color="gray.500">Dari semua transaksi</Text>
                </VStack>
              </Card.Body>
            </Card.Root>

            <Card.Root borderTop="4px" borderTopColor="orange.500">
              <Card.Body>
                <VStack align="start" gap={2}>
                  <Icon fontSize="2xl" color="orange.500">
                    <FiShoppingCart />
                  </Icon>
                  <Text fontSize="xs" color="gray.600" fontWeight="medium">
                    Total Transaksi
                  </Text>
                  <Heading size="md">{salesData.jumlah_transaksi}</Heading>
                  <Text fontSize="xs" color="gray.500">Jumlah transaksi</Text>
                </VStack>
              </Card.Body>
            </Card.Root>

            <Card.Root borderTop="4px" borderTopColor="green.500">
              <Card.Body>
                <VStack align="start" gap={2}>
                  <Icon fontSize="2xl" color="green.500">
                    <FiPackage />
                  </Icon>
                  <Text fontSize="xs" color="gray.600" fontWeight="medium">
                    Total Laba
                  </Text>
                  <Heading size="md">{formatCurrency(salesData.total_laba)}</Heading>
                  <Text fontSize="xs" color="gray.500">Laba kotor</Text>
                </VStack>
              </Card.Body>
            </Card.Root>

            <Card.Root borderTop="4px" borderTopColor="purple.500">
              <Card.Body>
                <VStack align="start" gap={2}>
                  <Icon fontSize="2xl" color="purple.500">
                    <FiBarChart2 />
                  </Icon>
                  <Text fontSize="xs" color="gray.600" fontWeight="medium">
                    Rata-rata Transaksi
                  </Text>
                  <Heading size="md">{formatCurrency(salesData.rata_rata_transaksi)}</Heading>
                  <Text fontSize="xs" color="gray.500">Per transaksi</Text>
                </VStack>
              </Card.Body>
            </Card.Root>
          </SimpleGrid>
        )}


      </VStack>
    </Box>
  );
}
