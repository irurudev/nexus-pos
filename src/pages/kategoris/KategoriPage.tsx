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
  createToaster,
  Table,
} from '@chakra-ui/react';
import ConfirmDialog from '../../components/common/ConfirmDialog';
import Pagination from '../../components/common/Pagination';
import { FiEdit2, FiTrash2, FiPlus } from 'react-icons/fi';
import usePermissions from '../../hooks/usePermissions';
import { kategoriAPI, type Kategori } from '../../services';
import KategoriForm from './KategoriForm';

const toaster = createToaster({
  placement: 'top-end',
  pauseOnPageIdle: true,
});

export default function KategoriPage() {
  const [kategoris, setKategoris] = useState<Kategori[]>([]);
  const [page, setPage] = useState(1);
  const [totalKategorisCount, setTotalKategorisCount] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const pageSize = 10;
  const [isLoading, setIsLoading] = useState(true);
  const [isDialogOpen, setDialogOpen] = useState(false);
  const [isDeleteOpen, setDeleteOpen] = useState(false);
  const [selectedKategori, setSelectedKategori] = useState<Kategori | null>(null);
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const perms = usePermissions();

  useEffect(() => {
    fetchKategoris();
  }, [page]);



  const fetchKategoris = async () => {
    try {
      setIsLoading(true);
      const response = await kategoriAPI.getAll({ per_page: pageSize, page });

      // PaginatedResponse<T> -> response.data is the items array
      const items: Kategori[] = response.data ?? [];
      setKategoris(items);

      const pagination = response.pagination ?? response.meta ?? null;

      if (pagination) {
        setTotalKategorisCount(pagination.total ?? 0);
        setLastPage(pagination.last_page ?? Math.max(1, Math.ceil((pagination.total ?? items.length) / pageSize)));
      } else {
        const total = items.length;
        setTotalKategorisCount(total);
        setLastPage(Math.max(1, Math.ceil(total / pageSize)));
      }
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toaster.error({
        title: 'Error',
        description: err.response?.data?.message || 'Gagal memuat kategori',
      });
    } finally {
      setIsLoading(false);
    }
  };

  // Pagination helpers
  const filteredKategoris = kategoris.filter(k => k.id_kategori != null);
  const totalKategoris = totalKategorisCount;
  const totalKategoriPages = lastPage || 1;
  const kategoriStart = (page - 1) * pageSize + 1;
  const visibleKategoris = filteredKategoris;

  const handleOpenCreate = () => {
    setSelectedKategori(null);
    setDialogOpen(true);
  };
  
  const handleOpenEdit = (kategori: Kategori) => {
    setSelectedKategori(kategori);
    setDialogOpen(true);
  };



  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await kategoriAPI.delete(deleteId);
      toaster.success({
        title: 'Sukses',
        description: 'Kategori berhasil dihapus',
      });
      setDeleteOpen(false);
      setDeleteId(null);
      fetchKategoris();
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toaster.error({
        title: 'Error',
        description: err.response?.data?.message || 'Gagal menghapus kategori',
      });
    }
  };

  return (
    <Box>
      <VStack align="stretch" gap={6}>
        {/* Header */}
        <HStack justify="space-between">
          <Box>
            <Heading size="lg" mb={2}>Kategori</Heading>
            <Text color="gray.600">Kelola kategori barang</Text>
          </Box>
          {perms.canCreate('kategori') ? (
            <Button colorScheme="blue" onClick={handleOpenCreate}>
              <FiPlus /> Tambah Kategori
            </Button>
          ) : (
            <Button colorScheme="blue" disabled title="Hanya admin yang dapat menambah kategori">+
              Tambah Kategori
            </Button>
          )}
        </HStack>

        {/* Table Card */}
        <Card.Root>
          <Card.Body>
            {isLoading ? (
              <Center py={10}>
                <Spinner size="lg" color="blue.500" />
              </Center>
            ) : kategoris.length === 0 ? (
              <Center py={10}>
                <Text color="gray.500">Belum ada data kategori</Text>
              </Center>
            ) : (
              <>
              <Box overflowX="auto">
                <Box minW={{ base: '600px', md: 'auto' }}>
                  <Table.Root>
                    <Table.Header>
                      <Table.Row bg="gray.50">
                        <Table.ColumnHeader width="60px">#</Table.ColumnHeader>
                        <Table.ColumnHeader>Nama Kategori</Table.ColumnHeader>
                        <Table.ColumnHeader width="100px">Aksi</Table.ColumnHeader>
                      </Table.Row>
                    </Table.Header>
                    <Table.Body>
                      {visibleKategoris.map((kategori, i) => (
                        <Table.Row key={kategori.id_kategori}>
                          <Table.Cell fontWeight="semibold">{kategoriStart + i}</Table.Cell>
                          <Table.Cell>{kategori.nama_kategori}</Table.Cell>
                          <Table.Cell>
                            <HStack gap={1}>
                              {perms.canEdit('kategori') ? (
                                <Button
                                  size="sm"
                                  variant="ghost"
                                  colorScheme="blue"
                                  onClick={() => handleOpenEdit(kategori)}
                                >
                                  <FiEdit2 />
                                </Button>
                              ) : null}
                              {perms.canDelete('kategori') ? (
                                <Button
                                  size="sm"
                                  variant="ghost"
                                  colorScheme="red"
                                  onClick={() => {
                                    setDeleteId(kategori.id_kategori);
                                    setDeleteOpen(true);
                                  }}
                                >
                                  <FiTrash2 />
                                </Button>
                              ) : null}
                              {!perms.canEdit('kategori') && !perms.canDelete('kategori') && (
                                <Text fontSize="sm" color="gray.500">Hanya admin</Text>
                              )}
                            </HStack>
                          </Table.Cell>
                        </Table.Row>
                      ))}
                    </Table.Body>
                  </Table.Root>
                </Box>
              </Box>

              {/* Pagination */}
              {totalKategoris > pageSize && (
                <Pagination page={page} setPage={setPage} lastPage={totalKategoriPages} total={totalKategoris} pageSize={pageSize} />
              )}
              </>
            )}
          </Card.Body>
        </Card.Root>
      </VStack>

      <KategoriForm
        isOpen={isDialogOpen}
        kategori={selectedKategori}
        onClose={() => setDialogOpen(false)}
        onSaved={() => { setDialogOpen(false); fetchKategoris(); }}
      />

      <ConfirmDialog
        isOpen={isDeleteOpen}
        onClose={() => { setDeleteOpen(false); setDeleteId(null); }}
        title="Hapus Kategori"
        description="Yakin hapus kategori ini?"
        onConfirm={handleDelete}
        confirmLabel="Hapus"
        cancelLabel="Batal"
        confirmColorScheme="red"
      />
    </Box>
  );
}
