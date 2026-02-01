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
  HStack,
} from '@chakra-ui/react';
import { SelectField, NumberField } from '../../components';


import { barangAPI, type Barang, type Kategori } from '../../services';

const toaster = createToaster({ placement: 'top-end', pauseOnPageIdle: true });

interface Props {
  isOpen: boolean;
  onClose: () => void;
  barang?: Barang | null;
  kategoris: Kategori[];
  onSaved?: () => void;
}

export default function BarangForm({ isOpen, onClose, barang, kategoris, onSaved }: Props) {
  const [formData, setFormData] = useState<{
    kode_barang: string;
    kategori_id: number;
    nama: string;
    harga_beli?: number;
    harga_jual?: number;
    stok: number;
  }>({ kode_barang: '', kategori_id: 0, nama: '', harga_beli: undefined, harga_jual: undefined, stok: 0 });

  useEffect(() => {
    if (barang) {
      setFormData({
        kode_barang: barang.kode_barang,
        kategori_id: barang.kategori_id,
        nama: barang.nama,
        harga_beli: barang.harga_beli,
        harga_jual: barang.harga_jual,
        stok: barang.stok,
      });
    } else {
      // do not default harga_beli/harga_jual to 0 — leave undefined so placeholder is shown
      setFormData({ kode_barang: '', kategori_id: 0, nama: '', harga_beli: undefined, harga_jual: undefined, stok: 0 });
    }
  }, [barang]);



  const handleSubmit = async () => {
    try {
      if (!formData.nama || !formData.kategori_id) {
        toaster.error({ title: 'Validasi Error', description: 'Nama dan kategori harus diisi' });
        return;
      }

      if (!formData.harga_beli || formData.harga_beli <= 0 || !formData.harga_jual || formData.harga_jual <= 0) {
        toaster.error({ title: 'Validasi Error', description: 'Harga beli dan harga jual harus lebih dari 0' });
        return;
      }

      if (barang) {
        await barangAPI.update(barang.kode_barang, {
          kategori_id: formData.kategori_id,
          nama: formData.nama,
          harga_beli: formData.harga_beli,
          harga_jual: formData.harga_jual,
          stok: formData.stok,
        });
        toaster.success({ title: 'Sukses', description: 'Barang berhasil diupdate' });
      } else {
        // omit kode_barang when creating — backend will generate it
        await barangAPI.create({
          kategori_id: formData.kategori_id,
          nama: formData.nama,
          harga_beli: formData.harga_beli,
          harga_jual: formData.harga_jual,
          stok: formData.stok,
        });
        toaster.success({ title: 'Sukses', description: 'Barang berhasil ditambahkan' });
      }

      onSaved?.();
      onClose();
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toaster.error({ title: 'Error', description: err.response?.data?.message || 'Gagal menyimpan barang' });
    }
  };


  return (
    <DialogRoot open={isOpen} onOpenChange={(e) => { if (!e.open) onClose(); }}>
      <DialogBackdrop />
      <DialogContent position="fixed" top={{ base: '10vh', md: '12vh' }} left="50%" transform="translateX(-50%)" zIndex="overlay" maxW={{ base: '95vw', md: '600px' }}>
        <DialogHeader>
          <DialogTitle color="gray.900">{barang ? 'Edit Barang' : 'Tambah Barang'}</DialogTitle>
          <DialogCloseTrigger />
        </DialogHeader>
        <DialogBody>
          <VStack gap={4}>
            <Field.Root>
              <Field.Label color="gray.900">Kode Barang</Field.Label>
              <Input
                placeholder={barang ? undefined : '(dibuat otomatis oleh sistem)'}
                value={formData.kode_barang}
                disabled
                color="gray.900"
              />
            </Field.Root>
            <Field.Root required>
              <Field.Label color="gray.900">Nama Barang</Field.Label>
              <Input placeholder="Nama barang" value={formData.nama}
                onChange={(e) => setFormData({ ...formData, nama: e.target.value })} color="gray.900" />
            </Field.Root>
            <Field.Root required>
              <SelectField
                label="Kategori"
                items={kategoris.filter(k => k.id_kategori != null).map(k => ({ label: k.nama_kategori, value: String(k.id_kategori) }))}
                value={String(formData.kategori_id)}
                onChange={(v) => setFormData({ ...formData, kategori_id: parseInt(v) || 0 })}
                placeholder="Pilih kategori"
                required
              />
            </Field.Root>

            <HStack gap={4} w="full">
              <Field.Root required flex={1}>
                <NumberField label="Harga Beli" min={1} value={formData.harga_beli} onChange={(v) => setFormData({ ...formData, harga_beli: Math.max(1, Math.trunc(v || 0)) })} placeholder="IDR -" required />
              </Field.Root>
              <Field.Root required flex={1}>
                <NumberField label="Harga Jual" min={1} value={formData.harga_jual} onChange={(v) => setFormData({ ...formData, harga_jual: Math.max(1, Math.trunc(v || 0)) })} placeholder="IDR -" required />
              </Field.Root>
            </HStack>

            <Field.Root required>
              <NumberField label="Stok" min={0} value={formData.stok} onChange={(v) => setFormData({ ...formData, stok: Math.trunc(v || 0) })} placeholder="-" required />
            </Field.Root>
          </VStack>
        </DialogBody>
        <DialogFooter>
          <Button variant="ghost" onClick={onClose}>Batal</Button>
          <Button colorScheme="blue" onClick={handleSubmit}>{barang ? 'Update' : 'Tambah'}</Button>
        </DialogFooter>
      </DialogContent>
    </DialogRoot>
  );
}
