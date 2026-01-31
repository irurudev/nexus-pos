import { useEffect, useState } from 'react';
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
} from '@chakra-ui/react';
import { kategoriAPI, type Kategori } from '../../services';

const toaster = createToaster({ placement: 'top-end', pauseOnPageIdle: true });

interface Props {
  isOpen: boolean;
  onClose: () => void;
  kategori?: Kategori | null;
  onSaved?: () => void;
}

export default function KategoriForm({ isOpen, onClose, kategori, onSaved }: Props) {
  const [formData, setFormData] = useState({ nama_kategori: '' });

  useEffect(() => {
    if (kategori) setFormData({ nama_kategori: kategori.nama_kategori });
    else setFormData({ nama_kategori: '' });
  }, [kategori]);

  const handleSubmit = async () => {
    try {
      if (!formData.nama_kategori) {
        toaster.error({ title: 'Validasi Error', description: 'Nama kategori harus diisi' });
        return;
      }

      if (kategori) {
        await kategoriAPI.update(kategori.id_kategori, formData);
        toaster.success({ title: 'Sukses', description: 'Kategori berhasil diupdate' });
      } else {
        await kategoriAPI.create(formData);
        toaster.success({ title: 'Sukses', description: 'Kategori berhasil ditambahkan' });
      }

      onSaved?.();
      onClose();
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toaster.error({ title: 'Error', description: err.response?.data?.message || 'Gagal menyimpan kategori' });
    }
  };

  return (
    <DialogRoot open={isOpen} onOpenChange={(e) => { if (!e.open) onClose(); }}>
      <DialogBackdrop />
      <DialogContent position="fixed" top={{ base: '10vh', md: '12vh' }} left="50%" transform="translateX(-50%)" zIndex="overlay" maxW={{ base: '95vw', md: '600px' }}>
        <DialogHeader>
          <DialogTitle color="gray.900">{kategori ? 'Edit Kategori' : 'Tambah Kategori'}</DialogTitle>
          <DialogCloseTrigger />
        </DialogHeader>
        <DialogBody>
          <VStack gap={4}>
            <Field.Root required>
              <Field.Label color="gray.900">Nama Kategori</Field.Label>
              <Input placeholder="Nama kategori" value={formData.nama_kategori} onChange={(e) => setFormData({ nama_kategori: e.target.value })} color="gray.900" />
            </Field.Root>
          </VStack>
        </DialogBody>
        <DialogFooter>
          <Button variant="ghost" onClick={onClose}>Batal</Button>
          <Button colorScheme="blue" onClick={handleSubmit}>{kategori ? 'Update' : 'Tambah'}</Button>
        </DialogFooter>
      </DialogContent>
    </DialogRoot>
  );
}
