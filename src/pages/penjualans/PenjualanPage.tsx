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
  Icon,
  DialogRoot,
  DialogBackdrop,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogBody,
  DialogFooter,
  DialogCloseTrigger,
  Input,
} from '@chakra-ui/react';
import * as Alert from '../../components/common/Alert';
import ConfirmDialog from '../../components/common/ConfirmDialog';
import Pagination from '../../components/common/Pagination';
import usePermissions from '../../hooks/usePermissions';
import { FiEye, FiTrash2, FiPlus, FiSearch } from 'react-icons/fi';
import { penjualanAPI, barangAPI, pelangganAPI, type Penjualan as PenjualanType, type Barang as BarangType, type Pelanggan as PelangganType } from '../../services';
import PenjualanForm from './PenjualanForm';

const toaster = createToaster({
  placement: 'top-end',
  pauseOnPageIdle: true,
});



export default function PenjualanPage() {
  const [penjualans, setPenjualans] = useState<PenjualanType[]>([]);
  const [barangs, setBarangs] = useState<BarangType[]>([]);
  const [pelanggans, setPelanggans] = useState<PelangganType[]>([]);
  const [page, setPage] = useState(1);
  const [totalPenjualansCount, setTotalPenjualansCount] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const [searchTerm, setSearchTerm] = useState('');
  const pageSize = 10;
  const [isLoading, setIsLoading] = useState(true);
  const [isDialogOpen, setDialogOpen] = useState(false);
  const perms = usePermissions();
  const [isDeleteOpen, setDeleteOpen] = useState(false);
  const [deleteId, setDeleteId] = useState<string | null>(null);
  const [formData, setFormData] = useState({
    id_pelanggan: '',
    items: [{ id_barang: '', jumlah: 1 }],
  });
  const [submitAttempted, setSubmitAttempted] = useState(0);
  // server-side validation errors to show inside the form
  const [serverErrors, setServerErrors] = useState<Record<string, string[]> | null>(null);

  // Detail view state
  const [isDetailOpen, setDetailOpen] = useState(false);
  const [selectedPenjualanDetail, setSelectedPenjualanDetail] = useState<PenjualanType | null>(null);
  const [isLoadingDetail, setLoadingDetail] = useState(false);

  // Fetch data (debounced on searchTerm)
  useEffect(() => {
    const t = setTimeout(() => fetchData(), 300);
    return () => clearTimeout(t);
  }, [page, searchTerm]);

  const fetchData = async () => {
    try {
      setIsLoading(true);
      const [penjualanRes, barangRes, pelangganRes] = await Promise.all([
        penjualanAPI.getAll({ per_page: pageSize, page, search: searchTerm || undefined }),
        barangAPI.getAll({ per_page: 1000 }),
        pelangganAPI.getAll({ per_page: 1000 }),
      ]);

      const items: PenjualanType[] = penjualanRes.data ?? [];
      setPenjualans(items);

      const pagination = penjualanRes.pagination ?? penjualanRes.meta ?? null;

      if (pagination) {
        setTotalPenjualansCount(pagination.total ?? 0);
        setLastPage(pagination.last_page ?? Math.max(1, Math.ceil((pagination.total ?? items.length) / pageSize)));
      } else {
        const total = items.length;
        setTotalPenjualansCount(total);
        setLastPage(Math.max(1, Math.ceil(total / pageSize)));
      }

      setBarangs(barangRes.data ?? []);
      setPelanggans(pelangganRes.data ?? []);
    } catch {
      toaster.error({ title: 'Error', description: 'Gagal memuat data' });
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    setPage(1);
  }, [penjualans]);

  const totalPenjualans = totalPenjualansCount;
  const totalPenjualanPages = lastPage || 1;
  const penjualanStart = (page - 1) * pageSize + 1;
  const visiblePenjualans = penjualans;

  const handleSubmit = async () => {
    // mark that user tried to submit so the form can show outlines
    setSubmitAttempted((s) => s + 1);
    // clear previous server errors when retrying
    setServerErrors(null);

    try {
      if (formData.items.length === 0) {
        toaster.error({ title: 'Error', description: 'Minimal 1 item harus dipilih' });
        return;
      }

      // validate items have selected barang and jumlah > 0
      for (const it of formData.items) {
        if (!it.id_barang) {
          toaster.error({ title: 'Error', description: 'Semua item harus memilih barang' });
          return;
        }
        if (!it.jumlah || it.jumlah < 1) {
          toaster.error({ title: 'Error', description: 'Jumlah harus minimal 1 untuk tiap item' });
          return;
        }
        // client-side check stock availability
        const barang = barangs.find(b => b.kode_barang === it.id_barang);
        if (barang && (it.jumlah > (barang.stok ?? 0))) {
          toaster.error({ title: 'Stok tidak cukup', description: `${barang.nama} hanya tersisa ${barang.stok ?? 0}` });
          return;
        }
      }

      // transform frontend model -> API payload
      const payload: any = {
        items: formData.items.map(it => {
          const barang = barangs.find(b => b.kode_barang === it.id_barang);

          return {
            kode_barang: it.id_barang,
            qty: it.jumlah,
            harga_satuan: barang ? barang.harga_jual : undefined,
            // jumlah will be computed by backend if not provided
          };
        }),
      };

      if (formData.id_pelanggan) payload.kode_pelanggan = formData.id_pelanggan;

      await penjualanAPI.create(payload);
      toaster.success({ title: 'Sukses', description: 'Penjualan berhasil ditambahkan' });
      setDialogOpen(false);
      fetchData();
    } catch (err: unknown) {
      const e = err as any;
      const message = e?.response?.data?.message || 'Gagal menyimpan penjualan';

      // If server returned validation errors, set them to show inside form (no toast)
      if (e?.response?.data?.errors) {
        const errs = e.response.data.errors as Record<string, string[]>;
        setServerErrors(errs);
        console.debug('Server validation errors:', errs);
      } else {
        toaster.error({ title: 'Error', description: message });
        console.debug('API error:', e);
      }
    }
  };

  const handleDelete = async () => {
    // API does not support deleting penjualan (only index/show/store are exposed).
    toaster.info({ title: 'Info', description: `Hapus penjualan ${deleteId ?? ''} tidak didukung oleh API` });
    setDeleteOpen(false);
    setDeleteId(null);
  };

  const handleView = async (id: string) => {
    try {
      setLoadingDetail(true);
      const res = await penjualanAPI.getById(id);
      setSelectedPenjualanDetail(res.data);
      setDetailOpen(true);
    } catch (err: unknown) {
      toaster.error({ title: 'Error', description: 'Gagal mengambil detail penjualan' });
    } finally {
      setLoadingDetail(false);
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
            <Heading size="lg" mb={2}>Penjualan</Heading>
            <Text color="gray.600">Kelola transaksi penjualan</Text>
          </Box>
          {perms.canCreate('penjualan') ? (
            <Button colorScheme="blue" onClick={() => {
              setFormData({ id_pelanggan: '', items: [{ id_barang: '', jumlah: 1 }] });
              setDialogOpen(true);
            }}>
              <FiPlus /> Tambah Penjualan
            </Button>
          ) : (
            <Button colorScheme="blue" disabled title="Tidak memiliki izin">+
              Tambah Penjualan
            </Button>
          )}
        </HStack>

        {/* Search (position like Barang page) */}
        <HStack>
          <Input
            placeholder="Cari ID / Pelanggan / Tanggal"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            color="gray.900"
          />
          <FiSearch />
        </HStack>

        <Card.Root>
          <Card.Body>
            {isLoading ? (
              <Center py={10}><Spinner size="lg" color="blue.500" /></Center>
            ) : penjualans.length === 0 ? (
              <Center py={10}><Text color="gray.500">Belum ada data</Text></Center>
            ) : (
              <>
              <Box overflowX="auto">
                <Box minW={{ base: '600px', md: 'auto' }}>
                  <Table.Root>
                    <Table.Header>
                      <Table.Row bg="gray.50">
                        <Table.ColumnHeader width="60px">#</Table.ColumnHeader>
                        <Table.ColumnHeader>ID</Table.ColumnHeader>
                        <Table.ColumnHeader>Pelanggan</Table.ColumnHeader>
                        <Table.ColumnHeader>Tanggal</Table.ColumnHeader>
                        <Table.ColumnHeader textAlign="end">Total</Table.ColumnHeader>
                        <Table.ColumnHeader width="100px">Aksi</Table.ColumnHeader>
                      </Table.Row>
                    </Table.Header>
                    <Table.Body>
                      {visiblePenjualans.map((p, i) => (
                        <Table.Row key={p.id_nota}>
                          <Table.Cell>{penjualanStart + i}</Table.Cell>
                          <Table.Cell fontWeight="semibold">{p.id_nota}</Table.Cell>
                          <Table.Cell>{p.pelanggan?.nama || p.kode_pelanggan}</Table.Cell>
                          <Table.Cell>{new Date(p.tgl).toLocaleDateString('id-ID')}</Table.Cell>
                          <Table.Cell textAlign="end" fontWeight="semibold" color="blue.600">
                            {formatCurrency(p.total_akhir)}
                      </Table.Cell>
                      <Table.Cell>
                        <HStack gap={1}>
                          <Button size="sm" variant="ghost" title="Lihat detail" _hover={{ bg: 'gray.100' }} onClick={() => handleView(p.id_nota)}>
                            <Icon as={FiEye} boxSize={4} color="gray.800" />
                          </Button>
                          <Button size="sm" variant="ghost" colorScheme="red" disabled title="Hapus tidak didukung"> <FiTrash2 /> </Button>
                        </HStack>
                      </Table.Cell>
                    </Table.Row>
                  ))}
                </Table.Body>
              </Table.Root>
                </Box>
              </Box>

              {/* Pagination */}
              {totalPenjualans > pageSize && (
                <Pagination page={page} setPage={setPage} lastPage={totalPenjualanPages} total={totalPenjualans} pageSize={pageSize} />
              )}
              </>
            )}
          </Card.Body>
        </Card.Root>
      </VStack>

      <DialogRoot open={isDialogOpen} onOpenChange={(v: any) => {
        const open = Boolean(v?.open ?? v);
        if (!open) {
          // blur any focused element to prevent aria-hidden conflicts
          try { (document.activeElement as HTMLElement | null)?.blur(); } catch (e) {}
        }
        setDialogOpen(open);
      }} size="lg">
        <DialogBackdrop />
        <DialogContent position="fixed" top={{ base: '8vh', md: '10vh' }} left="50%" transform="translateX(-50%)" zIndex="overlay" maxW={{ base: '95vw', md: '760px' }}>
          <DialogHeader>
            <DialogTitle color="gray.900">Tambah Penjualan</DialogTitle>
            <DialogCloseTrigger />
          </DialogHeader>
          <DialogBody>
            {serverErrors && (
              <Alert.Root status="error" style={{ marginBottom: 16 }}>
                <Alert.Indicator status="error" />
                <Box>
                  <Alert.Title color="gray.800">Validasi gagal</Alert.Title>
                  <Box mt={2}>
                    {Object.entries(serverErrors).map(([k, v]) => (
                      <Text key={k} fontSize="sm" color="gray.800">{v.join(', ')}</Text>
                    ))}
                  </Box>
                </Box>
              </Alert.Root>
            )}

            <PenjualanForm
              barangs={barangs}
              pelanggans={pelanggans}
              formData={formData}
              setFormData={setFormData}
              submitAttempted={submitAttempted}
              serverErrors={serverErrors}
            />
          </DialogBody>
          <DialogFooter>
            <Button variant="ghost" onClick={() => setDialogOpen(false)}>Batal</Button>
            <Button colorScheme="blue" onClick={handleSubmit}>Tambah</Button>
          </DialogFooter>
        </DialogContent>
      </DialogRoot>

      {/* Detail dialog */}
      <DialogRoot open={isDetailOpen} onOpenChange={(v: any) => {
        const open = Boolean(v?.open ?? v);
        if (!open) {
          try { (document.activeElement as HTMLElement | null)?.blur(); } catch (e) {}
        }
        setDetailOpen(open);
      }} size="lg">
        <DialogBackdrop />
        <DialogContent position="fixed" top={{ base: '8vh', md: '10vh' }} left="50%" transform="translateX(-50%)" zIndex="overlay" maxW={{ base: '95vw', md: '760px' }}>
          <DialogHeader>
            <DialogTitle color="gray.900">Detail Penjualan</DialogTitle>
            <DialogCloseTrigger />
          </DialogHeader>
          <DialogBody>
            {isLoadingDetail ? (
              <Center py={10}><Spinner /></Center>
            ) : selectedPenjualanDetail ? (
              <VStack gap={4} align="stretch">
                <HStack justify="space-between">
                  <Box>
                    <Text fontWeight="semibold">ID: {selectedPenjualanDetail.id_nota}</Text>
                    <Text color="gray.600">Tanggal: {new Date(selectedPenjualanDetail.tgl).toLocaleString('id-ID')}</Text>
                    <Text color="gray.600">Pelanggan: {selectedPenjualanDetail.pelanggan?.nama || selectedPenjualanDetail.kode_pelanggan || '-'}</Text>
                  </Box>
                  <Box textAlign="right">
                    <Text fontWeight="semibold">Total: {formatCurrency(Number(selectedPenjualanDetail.total_akhir ?? 0))}</Text>
                    <Text color="gray.600">Sub: {formatCurrency(Number(selectedPenjualanDetail.subtotal ?? 0))}</Text>
                  </Box>
                </HStack>

                <Box overflowX="auto">
                  <Box minW={{ base: '600px', md: 'auto' }}>
                    <Table.Root>
                      <Table.Header>
                        <Table.Row bg="gray.50">
                          <Table.ColumnHeader width="60px">#</Table.ColumnHeader>
                          <Table.ColumnHeader>Barang</Table.ColumnHeader>
                          <Table.ColumnHeader textAlign="end">Qty</Table.ColumnHeader>
                          <Table.ColumnHeader textAlign="end">Harga</Table.ColumnHeader>
                          <Table.ColumnHeader textAlign="end">Jumlah</Table.ColumnHeader>
                        </Table.Row>
                      </Table.Header>
                      <Table.Body>
                        {((selectedPenjualanDetail as any).itemPenjualans ?? (selectedPenjualanDetail as any).item_penjualans ?? []).map((it: any, i: number) => (
                          <Table.Row key={it.id ?? `${it.kode_barang}-${Math.random()}`}>
                            <Table.Cell>{i + 1}</Table.Cell>
                            <Table.Cell>
                              <Text fontWeight="semibold" color="gray.800">{it.kode_barang}</Text>
                              <Text fontSize="sm" color="gray.600">{it.nama_barang ?? it.barang?.nama ?? barangs.find((b: any) => b.kode_barang === it.kode_barang)?.nama ?? ''}</Text>
                            </Table.Cell>
                            <Table.Cell textAlign="end">
                              <Text color="gray.800" fontWeight="semibold">{it.qty ?? it.qty}</Text>
                            </Table.Cell>
                            <Table.Cell textAlign="end">
                              <Text color="gray.800" fontWeight="semibold">{formatCurrency(Number(it.harga_satuan ?? it.harga_satuan))}</Text>
                            </Table.Cell>
                        <Table.Cell textAlign="end">
                          <Text color="gray.800" fontWeight="semibold">{formatCurrency(Number(it.jumlah ?? it.jumlah))}</Text>
                        </Table.Cell>
                      </Table.Row>
                    ))}
                  </Table.Body>
                    </Table.Root>
                  </Box>
                </Box>
              </VStack>
            ) : (
              <Center py={10}><Text color="gray.500">Tidak ada data</Text></Center>
            )}
          </DialogBody>
          <DialogFooter>
            <Button variant="ghost" onClick={() => setDetailOpen(false)}>Tutup</Button>
          </DialogFooter>
        </DialogContent>
      </DialogRoot>

      <ConfirmDialog
        isOpen={isDeleteOpen}
        onClose={() => { setDeleteOpen(false); setDeleteId(null); }}
        title="Hapus Penjualan"
        description="Yakin hapus penjualan ini?"
        onConfirm={handleDelete}
        confirmLabel="Hapus"
        cancelLabel="Batal"
        confirmColorScheme="red"
      />
    </Box>
  );
}
