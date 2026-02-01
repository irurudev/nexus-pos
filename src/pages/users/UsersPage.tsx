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
import { FiEdit2, FiPlus } from 'react-icons/fi';
import usePermissions from '../../hooks/usePermissions';
import { usersAPI } from '../../services';
import UserForm from './UserForm';
import Pagination from '../../components/common/Pagination';

const toaster = createToaster({ placement: 'top-end', pauseOnPageIdle: true });

export default function UsersPage() {
  const [users, setUsers] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isDialogOpen, setDialogOpen] = useState(false);
  const [selectedUser, setSelectedUser] = useState<any | null>(null);
  const [searchTerm, setSearchTerm] = useState('');

  const [page, setPage] = useState(1);
  const [totalUsersCount, setTotalUsersCount] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const pageSize = 10;

  const perms = usePermissions();

  useEffect(() => {
    fetchData();
  }, [page, searchTerm]);

  useEffect(() => { setPage(1); }, [searchTerm]);

  const fetchData = async () => {
    try {
      setIsLoading(true);
      const res = await usersAPI.getAll({ per_page: pageSize, page, search: searchTerm });
      const items = res.data ?? [];
      setUsers(items);

      const pagination = res.pagination ?? res.meta ?? null;
      if (pagination) {
        setTotalUsersCount(pagination.total ?? 0);
        setLastPage(pagination.last_page ?? Math.max(1, Math.ceil((pagination.total ?? items.length) / pageSize)));
      } else {
        const total = items.length;
        setTotalUsersCount(total);
        setLastPage(Math.max(1, Math.ceil(total / pageSize)));
      }
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toaster.error({ title: 'Error', description: err.response?.data?.message || 'Gagal memuat pengguna' });
    } finally {
      setIsLoading(false);
    }
  };



  return (
    <Box>
      <VStack align="stretch" gap={6}>
        <HStack justify="space-between">
          <Box>
            <Heading size="lg" mb={2}>Pengguna</Heading>
            <Text color="gray.600">Kelola akun pengguna</Text>
          </Box>
          {perms.canCreate('user') ? (
            <Button colorScheme="blue" onClick={() => { setSelectedUser(null); setDialogOpen(true); }}>
              <FiPlus /> Tambah Pengguna
            </Button>
          ) : (
            <Button colorScheme="blue" disabled title="Hanya admin yang dapat menambah pengguna">+ Tambah Pengguna</Button>
          )}
        </HStack>

        <Card.Root>
          <Card.Body>
            <HStack mb={4} gap={3}>
              <Input placeholder="Cari pengguna..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} />
              {searchTerm && <CloseButton onClick={() => setSearchTerm('')} />}
            </HStack>

            {isLoading ? (
              <Center py={10}><Spinner size="lg" color="blue.500" /></Center>
            ) : users.length === 0 ? (
              <Center py={10}><Text color="gray.500">Belum ada pengguna</Text></Center>
            ) : (
              <>
                <Box overflowX="auto">
                  <Box minW={{ base: '700px', md: 'auto' }}>
                    <Table.Root>
                      <Table.Header>
                        <Table.Row bg="gray.50">
                          <Table.ColumnHeader width="60px">#</Table.ColumnHeader>
                          <Table.ColumnHeader>Nama</Table.ColumnHeader>
                          <Table.ColumnHeader>Username</Table.ColumnHeader>
                          <Table.ColumnHeader>Email</Table.ColumnHeader>
                          <Table.ColumnHeader>Role</Table.ColumnHeader>
                          <Table.ColumnHeader textAlign="center">Aktif</Table.ColumnHeader>
                          <Table.ColumnHeader width="120px">Aksi</Table.ColumnHeader>
                        </Table.Row>
                      </Table.Header>
                      <Table.Body>
                        {users.map((u, i) => (
                          <Table.Row key={u.id}>
                            <Table.Cell fontWeight="semibold">{(page - 1) * pageSize + i + 1}</Table.Cell>
                            <Table.Cell>{u.name}</Table.Cell>
                            <Table.Cell>{u.username}</Table.Cell>
                            <Table.Cell>{u.email}</Table.Cell>
                            <Table.Cell><Badge colorScheme={u.role === 'admin' ? 'green' : 'blue'}>{u.role}</Badge></Table.Cell>
                            <Table.Cell textAlign="center">{u.is_active ? 'Ya' : 'Tidak'}</Table.Cell>
                            <Table.Cell>
                              <HStack>
                                {perms.canEdit('user') ? (
                                  <Button size="sm" variant="ghost" colorScheme="blue" onClick={() => { setSelectedUser(u); setDialogOpen(true); }}>
                                    <FiEdit2 />
                                  </Button>
                                ) : null}

                                {/* Activation can be changed in the edit form */}
                                

                                {!perms.canEdit('user') && (<Text fontSize="sm" color="gray.500">Hanya admin</Text>)}
                              </HStack>
                            </Table.Cell>
                          </Table.Row>
                        ))}
                      </Table.Body>
                    </Table.Root>
                  </Box>
                </Box>

                {totalUsersCount > pageSize && (
                  <Pagination page={page} setPage={setPage} lastPage={lastPage} total={totalUsersCount} pageSize={pageSize} />
                )}
              </>
            )}
          </Card.Body>
        </Card.Root>
      </VStack>

      <UserForm isOpen={isDialogOpen} onClose={() => setDialogOpen(false)} user={selectedUser} onSaved={() => fetchData()} />
    </Box>
  );
}
