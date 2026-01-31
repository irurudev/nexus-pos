import { useEffect, useState } from 'react';
import {
  Box,
  Button,
  VStack,
  HStack,
  Heading,
  Text,
  Card,
  Spinner,
  Center,
  Input,
  Badge,
  createToaster,
  Table,
  CloseButton,
} from '@chakra-ui/react';
import ConfirmDialog from '../../components/common/ConfirmDialog';
import Pagination from '../../components/common/Pagination';
import { FiEdit2, FiTrash2, FiPlus, FiSearch } from 'react-icons/fi';
import usePermissions from '../../hooks/usePermissions';
import { barangAPI, kategoriAPI, type Barang, type Kategori } from '../../services';
import BarangForm from './BarangForm';

const toaster = createToaster({
  placement: 'top-end',
  pauseOnPageIdle: true,
});

export default function BarangPage() {
  const [barangs, setBarangs] = useState<Barang[]>([]);
  const [kategoris, setKategoris] = useState<Kategori[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isDialogOpen, setDialogOpen] = useState(false);
  const [isDeleteOpen, setDeleteOpen] = useState(false);
  const [selectedBarang, setSelectedBarang] = useState<Barang | null>(null);
  const [deleteId, setDeleteId] = useState<string | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const [page, setPage] = useState(1);
  const [totalBarangsCount, setTotalBarangsCount] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const pageSize = 10;

  useEffect(() => {
    fetchData();
  }, [page, searchTerm]);

  useEffect(() => {
    // Reset to first page when search term changes
    setPage(1);
  }, [searchTerm]);

  useEffect(() => {
    console.log('barangs state updated:', barangs);
  }, [barangs]);

  const fetchData = async () => {
    try {
      setIsLoading(true);
      setErrorMessage(null);
      const [barangRes, kategoriRes] = await Promise.all([
        barangAPI.getAll({ per_page: pageSize, page, search: searchTerm }),
        kategoriAPI.getAll({ per_page: 1000 }),
      ]);
      console.log('fetched barangRes:', barangRes);
      console.log('fetched kategoriRes:', kategoriRes);

      // PaginatedResponse<T> -> response.data is items array
      const items: Barang[] = barangRes.data ?? [];
      setBarangs(items);

      // Try multiple locations for pagination metadata
      const pagination = barangRes.pagination ?? barangRes.meta ?? null;

      if (pagination) {
        setTotalBarangsCount(pagination.total ?? 0);
        setLastPage(pagination.last_page ?? Math.max(1, Math.ceil((pagination.total ?? items.length) / pageSize)));
      } else {
        const total = items.length;
        setTotalBarangsCount(total);
        setLastPage(Math.max(1, Math.ceil(total / pageSize)));
      }

      // Kategoris
      setKategoris(kategoriRes.data ?? []);
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      const msg = err.response?.data?.message || 'Gagal memuat data';
      toaster.error({
        title: 'Error',
        description: msg,
      });
      setErrorMessage(msg);
    } finally {
      setIsLoading(false);
    }
  };

  const filteredBarangs = barangs; // server-side filtered

  // Pagination helpers for barangs
  const totalBarangs = totalBarangsCount;
  const totalBarangPages = lastPage || 1;
  const barangStart = (page - 1) * pageSize + 1;
  const visibleBarangs = filteredBarangs;

  const handleOpenCreate = () => {
    setSelectedBarang(null);
    setDialogOpen(true);
  };

  const perms = usePermissions();

  const handleOpenEdit = (barang: Barang) => {
    setSelectedBarang(barang);
    setDialogOpen(true);
  };

  // create/update handled by BarangForm

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await barangAPI.delete(deleteId);
      toaster.success({ title: 'Sukses', description: 'Barang berhasil dihapus' });
      setErrorMessage(null);
      setDeleteOpen(false);
      setDeleteId(null);
      fetchData();
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      const msg = err.response?.data?.message || 'Gagal menghapus barang';
      toaster.error({
        title: 'Error',
        description: msg,
      });
      setErrorMessage(msg);
    }
  };

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(value);
  };

  return (
    <Box>
      <VStack align="stretch" gap={6}>
        <HStack justify="space-between">
          <Box>
            <Heading size="lg" mb={2}>Barang</Heading>
            <Text color="gray.600">Kelola stok barang</Text>
          </Box>
          {perms.canCreate('barang') ? (
            <Button colorScheme="blue" onClick={handleOpenCreate}>
              <FiPlus /> Tambah Barang
            </Button>
          ) : (
            <Button colorScheme="blue" disabled title="Hanya admin yang dapat menambah barang">+
              Tambah Barang
            </Button>
          )}
        </HStack>

        {/* Search */}
        <HStack>
          <Input
            placeholder="Cari barang..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            color="gray.900"
          />
          <FiSearch />
        </HStack>

        {errorMessage && (
          <Box bg="red.50" border="1px" borderColor="red.200" p={3} borderRadius="md">
            <Box display="flex" alignItems="center" justifyContent="space-between">
              <Box>
                <Text fontWeight="bold" color="red.700">Error</Text>
                <Text color="red.700">{errorMessage}</Text>
              </Box>
              <CloseButton onClick={() => setErrorMessage(null)} />
            </Box>
          </Box>
        )}

        <Card.Root>
          <Card.Body>
            {isLoading ? (
              <Center py={10}>
                <Spinner size="lg" color="blue.500" />
              </Center>
            ) : filteredBarangs.length === 0 ? (
              <Center py={10}>
                <Text color="gray.500">Belum ada data barang</Text>
              </Center>
            ) : (
              <>
              <Table.Root>
                <Table.Header>
                  <Table.Row bg="gray.50">
                    <Table.ColumnHeader width="60px">#</Table.ColumnHeader>
                    <Table.ColumnHeader>Kode</Table.ColumnHeader>
                    <Table.ColumnHeader>Nama Barang</Table.ColumnHeader>
                    <Table.ColumnHeader>Kategori</Table.ColumnHeader>
                    <Table.ColumnHeader textAlign="end">Harga Jual</Table.ColumnHeader>
                    <Table.ColumnHeader textAlign="end">Stok</Table.ColumnHeader>
                    <Table.ColumnHeader width="100px">Aksi</Table.ColumnHeader>
                  </Table.Row>
                </Table.Header>
                <Table.Body>
                  {visibleBarangs.map((barang, i) => (
                    <Table.Row key={barang.kode_barang}>
                      <Table.Cell>{barangStart + i}</Table.Cell>
                      <Table.Cell fontWeight="semibold">{barang.kode_barang}</Table.Cell>
                      <Table.Cell>{barang.nama}</Table.Cell>
                      <Table.Cell>
                        <Badge>{barang.kategori?.nama_kategori || `Kategori ${barang.kategori_id}`}</Badge>
                      </Table.Cell>
                      <Table.Cell textAlign="end" fontWeight="semibold" color="blue.600">
                        {formatCurrency(barang.harga_jual)}
                      </Table.Cell>
                      <Table.Cell textAlign="end">
                        <Badge colorScheme={barang.stok > 10 ? 'green' : barang.stok > 5 ? 'yellow' : 'red'}>
                          {barang.stok}
                        </Badge>
                      </Table.Cell>
                      <Table.Cell>
                        <HStack gap={1}>
                          {perms.canEdit('barang') ? (
                            <>
                              <Button size="sm" variant="ghost" colorScheme="blue" onClick={() => handleOpenEdit(barang)}>
                                <FiEdit2 />
                              </Button>
                              <Button
                                size="sm"
                                variant="ghost"
                                colorScheme="red"
                                onClick={() => {
                                  setDeleteId(barang.kode_barang);
                                  setDeleteOpen(true);
                                }}
                              >
                                <FiTrash2 />
                              </Button>
                            </>
                          ) : (
                            <Text fontSize="sm" color="gray.500">Hanya admin</Text>
                          )}
                        </HStack>
                      </Table.Cell>
                    </Table.Row>
                  ))}
                </Table.Body>
              </Table.Root>

              {/* Pagination */}
              {totalBarangs > pageSize && (
                <Pagination page={page} setPage={setPage} lastPage={totalBarangPages} total={totalBarangs} pageSize={pageSize} />
              )} 
              </>
            )}
          </Card.Body>
        </Card.Root>
      </VStack>

      <BarangForm
        isOpen={isDialogOpen}
        barang={selectedBarang}
        kategoris={kategoris}
        onClose={() => setDialogOpen(false)}
        onSaved={() => { setDialogOpen(false); fetchData(); }}
      />

      <ConfirmDialog
        isOpen={isDeleteOpen}
        onClose={() => { setDeleteOpen(false); setDeleteId(null); }}
        title="Hapus Barang"
        description="Apakah Anda yakin ingin menghapus barang ini?"
        onConfirm={handleDelete}
        confirmLabel="Hapus"
        cancelLabel="Batal"
        confirmColorScheme="red"
      />
    </Box>
  );
}
