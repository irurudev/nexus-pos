import { useEffect, useState, type ChangeEvent } from 'react';
import {
  VStack,
  Field,
  Input,
  Button,
  DialogRoot,
  DialogContent,
  DialogHeader,
  DialogBody,
  DialogFooter,
  DialogBackdrop,
  DialogTitle,
  DialogCloseTrigger,
  createToaster,
  HStack,
  Checkbox,
} from '@chakra-ui/react';
import { SelectField, TextField } from '../../components';
import { usersAPI } from '../../services';

const toaster = createToaster({ placement: 'top-end', pauseOnPageIdle: true });

interface Props {
  isOpen: boolean;
  onClose: () => void;
  user?: any | null;
  onSaved?: () => void;
}

export default function UserForm({ isOpen, onClose, user, onSaved }: Props) {
  const [formData, setFormData] = useState({
    name: '',
    username: '',
    email: '',
    password: '',
    role: 'kasir',
    is_active: true,
  });

  useEffect(() => {
    if (user) {
      setFormData({
        name: user.name,
        username: user.username,
        email: user.email,
        password: '',
        role: user.role || 'kasir',
        is_active: !!user.is_active,
      });
    } else {
      setFormData({ name: '', username: '', email: '', password: '', role: 'kasir', is_active: true });
    }
  }, [user]);

  const [apiErrors, setApiErrors] = useState<Record<string, string[]> | null>(null);

  const clearApiError = (key: string) => {
    setApiErrors((p) => {
      if (!p) return null;
      const np = { ...p } as Record<string, string[]>;
      delete (np as any)[key];
      return Object.keys(np).length ? np : null;
    });
  };

  const handleSubmit = async () => {
    try {
      // simple local check to avoid unnecessary requests
      if (!formData.name || !formData.username || !formData.email || (!user && !formData.password)) {
        toaster.error({ title: 'Validasi Error', description: 'Field harus diisi' });
        return;
      }

      setApiErrors(null);

      if (user) {
        await usersAPI.update(user.id, {
          ...formData,
          password: formData.password || undefined,
        });
        toaster.success({ title: 'Sukses', description: 'Pengguna berhasil diupdate' });
      } else {
        await usersAPI.create({
          ...formData,
        });
        toaster.success({ title: 'Sukses', description: 'Pengguna berhasil ditambahkan' });
      }

      onSaved?.();
      onClose();
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
      const resp = err.response?.data;
      if (resp?.errors) {
        setApiErrors(resp.errors);
        toaster.error({ title: 'Validasi Error', description: resp.message || 'Silakan perbaiki field yang berwarna merah' });
        return;
      }

      toaster.error({ title: 'Error', description: resp?.message || 'Gagal menyimpan' });
    }
  };

  return (
    <DialogRoot open={isOpen} onOpenChange={(e) => { if (!e.open) onClose(); }}>
      <DialogBackdrop />
      <DialogContent position="fixed" top={{ base: '10vh', md: '12vh' }} left="50%" transform="translateX(-50%)" zIndex="overlay" maxW={{ base: '95vw', md: '600px' }}>
        <DialogHeader>
          <DialogTitle color="gray.900">{user ? 'Edit' : 'Tambah'} Pengguna</DialogTitle>
          <DialogCloseTrigger />
        </DialogHeader>
        <DialogBody>
          <VStack gap={4}>
            <TextField label="Nama" placeholder="Nama pengguna" value={formData.name} onChange={(v) => { setFormData({ ...formData, name: v }); clearApiError('name'); }} required error={apiErrors?.name} />
            <TextField label="Username" placeholder="Username" value={formData.username} onChange={(v) => { setFormData({ ...formData, username: v }); clearApiError('username'); }} required error={apiErrors?.username} />
            <TextField label="Email" placeholder="email@domain" value={formData.email} onChange={(v) => { setFormData({ ...formData, email: v }); clearApiError('email'); }} required error={apiErrors?.email} />
            <Field.Root invalid={Boolean(apiErrors?.password)}>
              <Field.Label color="gray.900">Password {user ? '(kosongkan jika tidak diubah)' : ''}</Field.Label>
              <Input type="password" placeholder={user ? '•••••• (kosongkan jika tidak ingin mengubah)' : '••••••'} value={formData.password} onChange={(e) => { setFormData({ ...formData, password: e.target.value }); clearApiError('password'); }} />
              {apiErrors?.password && <Field.ErrorText>{apiErrors.password[0]}</Field.ErrorText>}
            </Field.Root>

            <SelectField label="Role" items={[{ label: 'Admin', value: 'admin' }, { label: 'Kasir', value: 'kasir' }]} value={formData.role} onChange={(v) => setFormData({ ...formData, role: v })} placeholder="Pilih role" required />

            <Field.Root>
              <HStack w="full" justify="space-between" align="center">
                <Field.Label color="gray.900">Aktif</Field.Label>

                <Checkbox.Root key={`is_active_${user?.id ?? 'new'}`} defaultChecked={formData.is_active}>
                  <Checkbox.HiddenInput
                    aria-label="Aktif"
                    defaultChecked={formData.is_active}
                    onChange={(e: ChangeEvent<HTMLInputElement>) => setFormData({ ...formData, is_active: e.target.checked })}
                    disabled={!user}
                  />

                  <Checkbox.Control />
                </Checkbox.Root>
              </HStack>
            </Field.Root>
          </VStack>
        </DialogBody>
        <DialogFooter>
          <Button variant="ghost" onClick={onClose}>Batal</Button>
          <Button colorScheme="blue" onClick={handleSubmit}>{user ? 'Update' : 'Tambah'}</Button>
        </DialogFooter>
      </DialogContent>
    </DialogRoot>
  );
}
