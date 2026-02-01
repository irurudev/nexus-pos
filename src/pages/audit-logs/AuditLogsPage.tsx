import { useEffect, useState } from 'react';
import { Box, Heading, Text, VStack, HStack, Table, Center, Spinner, Input, Button, Icon, DialogRoot, DialogBackdrop, DialogContent, DialogHeader, DialogTitle, DialogBody, DialogFooter, DialogCloseTrigger, createToaster } from '@chakra-ui/react';
import { FiEye } from 'react-icons/fi';
import Pagination from '../../components/common/Pagination';
import { auditAPI } from '../../services';
import usePermissions from '../../hooks/usePermissions';

const toaster = createToaster({ placement: 'top-end' });

export default function AuditLogsPage() {
  const perms = usePermissions();

  // Only admin can access this page
  if (perms.role !== 'admin') {
    return (
      <Box>
        <Heading size="md">Akses ditolak</Heading>
        <Text color="gray.600">Hanya admin yang dapat melihat audit logs.</Text>
      </Box>
    );
  }

  const [logs, setLogs] = useState<any[]>([]);
  const [page, setPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const [isLoading, setIsLoading] = useState(true);
  const [userId, setUserId] = useState<string>('');
  const [auditableType, setAuditableType] = useState<string>('');
  const pageSize = 10;
  const [selectedLog, setSelectedLog] = useState<any | null>(null);
  const [isDetailOpen, setDetailOpen] = useState(false);

  useEffect(() => {
    fetchLogs();
  }, [page]);

  const fetchLogs = async () => {
    if (!perms.role || perms.role !== 'admin') return;
    try {
      setIsLoading(true);
      const res = await auditAPI.getAll({ per_page: pageSize, page, user_id: userId ? Number(userId) : undefined, auditable_type: auditableType || undefined });
      setLogs(res.data ?? []);
      const pagination = res.pagination ?? res.meta ?? null;
      if (pagination) {
        setTotal(pagination.total ?? 0);
        setLastPage(pagination.last_page ?? 1);
      } else {
        setTotal((res.data ?? []).length);
        setLastPage(1);
      }
    } catch (err: any) {
      toaster.error({ title: 'Error', description: err?.response?.data?.message ?? 'Gagal memuat audit logs' });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <Box>
      <VStack align="stretch" gap={6}>
        <HStack justify="space-between">
          <Box>
            <Heading size="lg">Audit Logs</Heading>
            <Text color="gray.600">Riwayat perubahan sistem</Text>
          </Box>
        </HStack>

        <Box>
          <HStack gap={3} mb={4}>
            <Input placeholder="Filter user_id" value={userId} onChange={(e) => setUserId(e.target.value)} />
            <Input placeholder="Filter resource (auditable_type)" value={auditableType} onChange={(e) => setAuditableType(e.target.value)} />
            <Button onClick={() => { setPage(1); fetchLogs(); }}>Filter</Button>
          </HStack>

          {isLoading ? (
            <Center py={8}><Spinner /></Center>
          ) : logs.length === 0 ? (
            <Center py={8}><Text color="gray.500">Tidak ada data</Text></Center>
          ) : (
            <>
              <Box overflowX="auto">
                <Box minW={{ base: '600px', md: 'auto' }}>
                  <Table.Root>
                    <Table.Header>
                      <Table.Row bg="gray.50">
                        <Table.ColumnHeader><Text color="gray.900">Waktu</Text></Table.ColumnHeader>
                        <Table.ColumnHeader><Text color="gray.900">User</Text></Table.ColumnHeader>
                        <Table.ColumnHeader><Text color="gray.900">Aksi</Text></Table.ColumnHeader>
                        <Table.ColumnHeader><Text color="gray.900">Resource</Text></Table.ColumnHeader>
                        <Table.ColumnHeader><Text color="gray.900">ID</Text></Table.ColumnHeader>
                        <Table.ColumnHeader><Text color="gray.900">IP</Text></Table.ColumnHeader>
                        <Table.ColumnHeader><Text color="gray.900">Detail</Text></Table.ColumnHeader>
                      </Table.Row>
                    </Table.Header>
                    <Table.Body>
                      {logs.map((l: any) => (
                        <Table.Row key={l.id}>
                          <Table.Cell><Text color="gray.900">{new Date(l.created_at).toLocaleString()}</Text></Table.Cell>
                          <Table.Cell><Text color="gray.900">{l.user?.name ?? l.user_id ?? '-'}</Text></Table.Cell>
                          <Table.Cell><Text color="gray.900">{l.action}</Text></Table.Cell>
                          <Table.Cell><Text color="gray.900">{l.auditable_type.split('\\').pop()}</Text></Table.Cell>
                          <Table.Cell><Text color="gray.900">{l.auditable_id}</Text></Table.Cell>
                          <Table.Cell><Text color="gray.900">{l.ip_address}</Text></Table.Cell>
                          <Table.Cell>
                            <Button size="sm" variant="ghost" title="Lihat detail" onClick={() => { setSelectedLog(l); setDetailOpen(true); }}>
                              <Icon as={FiEye} boxSize={4} color="gray.900" />
                            </Button>
                          </Table.Cell>
                        </Table.Row>
                      ))}
                    </Table.Body>
                  </Table.Root>
                </Box>
              </Box>

              {total > pageSize && (
                <Pagination page={page} setPage={setPage} lastPage={lastPage} total={total} pageSize={pageSize} />
              )}

              {/* Detail dialog */}
              <DialogRoot open={isDetailOpen} onOpenChange={(v: any) => setDetailOpen(Boolean(v?.open ?? v))}>
                <DialogBackdrop />
                <DialogContent position="fixed" top={{ base: '8vh', md: '10vh' }} left="50%" transform="translateX(-50%)" zIndex="overlay" maxW={{ base: '95vw', md: '720px' }}>
                  <DialogHeader>
                    <DialogTitle color="gray.900">Detail Audit Log</DialogTitle>
                    <DialogCloseTrigger />
                  </DialogHeader>
                  <DialogBody>
                    {selectedLog ? (
                      <VStack align="stretch" gap={3}>
                        <Text color="gray.900"><Text as="span" fontWeight="semibold">Waktu: </Text>{new Date(selectedLog.created_at).toLocaleString()}</Text>
                        <Text color="gray.900"><Text as="span" fontWeight="semibold">User: </Text>{selectedLog.user?.name ?? selectedLog.user_id ?? '-'}</Text>
                        <Text color="gray.900"><Text as="span" fontWeight="semibold">Aksi: </Text>{selectedLog.action}</Text>
                        <Text color="gray.900"><Text as="span" fontWeight="semibold">Resource: </Text>{selectedLog.auditable_type.split('\\').pop()}</Text>
                        <Text color="gray.900"><Text as="span" fontWeight="semibold">ID: </Text>{selectedLog.auditable_id}</Text>
                        <Text color="gray.900"><Text as="span" fontWeight="semibold">IP: </Text>{selectedLog.ip_address}</Text>

                        <Box>
                          <Text color="gray.900" fontWeight="semibold">Old / New</Text>
                          <Text as="pre" color="gray.900" style={{ whiteSpace: 'pre-wrap', maxHeight: '40vh', overflow: 'auto' }}>{JSON.stringify({ old: selectedLog.old_values, new: selectedLog.new_values }, null, 2)}</Text>
                        </Box>
                      </VStack>
                    ) : (
                      <Center py={8}><Spinner /></Center>
                    )}
                  </DialogBody>
                  <DialogFooter>
                    <Button variant="ghost" color="gray.900" onClick={() => { setDetailOpen(false); setSelectedLog(null); }}>Tutup</Button>
                  </DialogFooter>
                </DialogContent>
              </DialogRoot>

            </>
          )}
        </Box>
      </VStack>
    </Box>
  );
}
