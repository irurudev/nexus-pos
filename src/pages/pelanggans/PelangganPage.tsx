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
  Badge,
  createToaster,
  Table,
} from '@chakra-ui/react';
import usePermissions from '../../hooks/usePermissions';
import { FiEdit2, FiTrash2, FiPlus } from 'react-icons/fi';
import { pelangganAPI, type Pelanggan as PelangganType } from '../../services';
import PelangganForm from './PelangganForm';
import ConfirmDialog from '../../components/common/ConfirmDialog';
import Pagination from '../../components/common/Pagination';

const toaster = createToaster({
  placement: 'top-end',
  pauseOnPageIdle: true,
});

export default function PelangganPage() {
  const [pelanggans, setPelanggans] = useState<PelangganType[]>([]);
  const [page, setPage] = useState(1);
  const [totalPelanggansCount, setTotalPelanggansCount] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const pageSize = 10;
  const [isLoading, setIsLoading] = useState(true);
  const [isDialogOpen, setDialogOpen] = useState(false);
  const perms = usePermissions();
  const [isDeleteOpen, setDeleteOpen] = useState(false);
  const [selectedPelanggan, setSelectedPelanggan] = useState<PelangganType | null>(null);
  const [deleteId, setDeleteId] = useState<string | null>(null);
 

  useEffect(() => {
    fetchPelanggans();
  }, [page]);

  const fetchPelanggans = async () => {
    try {
      setIsLoading(true);
      const response = await pelangganAPI.getAll({ per_page: pageSize, page });

      const items: PelangganType[] = response.data ?? [];
      setPelanggans(items);

      const pagination = response.pagination ?? response.meta ?? null;

      if (pagination) {
        setTotalPelanggansCount(pagination.total ?? 0);
        setLastPage(pagination.last_page ?? Math.max(1, Math.ceil((pagination.total ?? items.length) / pageSize)));
      } else {
        const total = items.length;
        setTotalPelanggansCount(total);
        setLastPage(Math.max(1, Math.ceil(total / pageSize)));
      }
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toaster.error({
        title: 'Error',
        description: err.response?.data?.message || 'Gagal memuat pelanggan',
      });
    } finally {
      setIsLoading(false);
    }
  };

  // Pagination helpers
  const totalPelanggans = totalPelanggansCount;
  const totalPelangganPages = lastPage || 1;
  const pelangganStart = (page - 1) * pageSize + 1;
  const visiblePelanggans = pelanggans;

  

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await pelangganAPI.delete(deleteId);
      toaster.success({ title: 'Sukses', description: 'Pelanggan berhasil dihapus' });
      setDeleteOpen(false);
      setDeleteId(null);
      fetchPelanggans();
    } catch {
      toaster.error({ title: 'Error', description: 'Gagal menghapus' });
    }
  };

  return (
    <Box>
      <VStack align="stretch" gap={6}>
        <HStack justify="space-between">
          <Box>
            <Heading size="lg" mb={2}>Pelanggan</Heading>
            <Text color="gray.600">Kelola data pelanggan</Text>
          </Box>
          {perms.canCreate('pelanggan') ? (
            <Button colorScheme="blue" onClick={() => {
              setSelectedPelanggan(null);
              setDialogOpen(true);
            }}>
              <FiPlus /> Tambah Pelanggan
            </Button>
          ) : (
            <Button colorScheme="blue" disabled title="Hanya admin yang dapat menambah pelanggan">+
              Tambah Pelanggan
            </Button>
          )}
        </HStack>

        <Card.Root>
          <Card.Body>
            {isLoading ? (
              <Center py={10}><Spinner size="lg" color="blue.500" /></Center>
            ) : pelanggans.length === 0 ? (
              <Center py={10}><Text color="gray.500">Belum ada data</Text></Center>
            ) : (
              <>
              <Table.Root>
                <Table.Header>
                  <Table.Row bg="gray.50">
                    <Table.ColumnHeader width="60px">#</Table.ColumnHeader>
                    <Table.ColumnHeader>ID</Table.ColumnHeader>
                    <Table.ColumnHeader>Nama</Table.ColumnHeader>
                    <Table.ColumnHeader>Gender</Table.ColumnHeader>
                    <Table.ColumnHeader>Domisili</Table.ColumnHeader>
                    <Table.ColumnHeader textAlign="end">Poin</Table.ColumnHeader>
                    <Table.ColumnHeader width="100px">Aksi</Table.ColumnHeader>
                  </Table.Row>
                </Table.Header>
                <Table.Body>
                  {visiblePelanggans.map((p, i) => (
                    <Table.Row key={p.id_pelanggan}>
                      <Table.Cell>{pelangganStart + i}</Table.Cell>
                      <Table.Cell fontWeight="semibold">{p.id_pelanggan}</Table.Cell>
                      <Table.Cell>{p.nama}</Table.Cell>
                      <Table.Cell>
                        <Badge colorScheme={p.jenis_kelamin === 'PRIA' ? 'blue' : 'pink'}>
                          {p.jenis_kelamin}
                        </Badge>
                      </Table.Cell>
                      <Table.Cell color="gray.600">{p.domisili}</Table.Cell>
                      <Table.Cell textAlign="end">
                        <Badge colorScheme="orange">{p.poin} poin</Badge>
                      </Table.Cell>
                      <Table.Cell>
                        <HStack gap={1}>
                          {perms.canEdit('pelanggan') ? (
                            <Button size="sm" variant="ghost" colorScheme="blue" onClick={() => {
                              setSelectedPelanggan(p);
                              setDialogOpen(true);
                            }}>
                              <FiEdit2 />
                            </Button>
                          ) : null}
                          {perms.canDelete('pelanggan') ? (
                            <Button size="sm" variant="ghost" colorScheme="red" onClick={() => {
                              setDeleteId(p.id_pelanggan);
                              setDeleteOpen(true);
                            }}>
                              <FiTrash2 />
                            </Button>
                          ) : null}
                          {!perms.canEdit('pelanggan') && !perms.canDelete('pelanggan') && (
                            <Text fontSize="sm" color="gray.500">Hanya admin</Text>
                          )}
                        </HStack>
                      </Table.Cell>
                    </Table.Row>
                  ))}
                </Table.Body>
              </Table.Root>

              {/* Pagination */}
              {totalPelanggans > pageSize && (
                <Pagination page={page} setPage={setPage} lastPage={totalPelangganPages} total={totalPelanggans} pageSize={pageSize} />
              )} 
              </>
            )}
          </Card.Body>
        </Card.Root>
      </VStack>

      <PelangganForm
        isOpen={isDialogOpen}
        pelanggan={selectedPelanggan}
        onClose={() => setDialogOpen(false)}
        onSaved={() => { setDialogOpen(false); fetchPelanggans(); }}
      />

      <ConfirmDialog
        isOpen={isDeleteOpen}
        onClose={() => { setDeleteOpen(false); setDeleteId(null); }}
        title="Hapus Pelanggan"
        description="Yakin hapus pelanggan ini?"
        onConfirm={handleDelete}
        confirmLabel="Hapus"
        cancelLabel="Batal"
        confirmColorScheme="red"
      />
    </Box>
  );
}
