import { useState, useEffect } from 'react';
import {
  VStack,
  Box,
  Button,
  Field,
  Text,
  createToaster,
  ScrollArea,
} from '@chakra-ui/react';
import { SelectField, NumberField } from '../../components';
import type { Barang, Pelanggan } from '../../services';

interface Item {
  id_barang: string;
  jumlah: number;
}

interface FormData {
  id_pelanggan: string;
  items: Item[];
}

interface PenjualanFormProps {
  barangs: Barang[];
  pelanggans: Pelanggan[];
  formData: FormData;
  setFormData: (data: FormData) => void;
}

export default function PenjualanForm({ barangs, pelanggans, formData, setFormData, submitAttempted }: PenjualanFormProps & { submitAttempted?: number }) {
  const toaster = createToaster({ placement: 'top-end', pauseOnPageIdle: true });
  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(value);
  };

  const [errors, setErrors] = useState<{ items: { id_barang?: boolean; jumlah?: boolean }[]; form?: boolean }>(() => ({ items: formData.items.map(() => ({})), form: false }));

  // available barangs: only those with stock > 0
  const availableBarangs = barangs.filter((b) => (b.stok ?? 0) > 0 && b.kode_barang);

  // if barangs change and some selected items are no longer available, clear them
  useEffect(() => {
    const availableCodes = new Set(availableBarangs.map((b) => b.kode_barang));
    let changed = false;
    const updated = formData.items.map((it) => {
      if (it.id_barang && !availableCodes.has(it.id_barang)) {
        changed = true;
        return { ...it, id_barang: '', jumlah: 1 };
      }
      return it;
    });

    if (changed) {
      setFormData({ ...formData, items: updated });
      toaster.error({ title: 'Barang tidak tersedia', description: 'Beberapa barang yang dipilih sudah habis dan telah dihapus dari form' });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [barangs]);

  // keep errors length in sync with items
  useEffect(() => {
    setErrors((prev: { items: { id_barang?: boolean; jumlah?: boolean }[]; form?: boolean }) => ({ items: formData.items.map((_it, i) => prev.items[i] ?? {}), form: prev.form }));
  }, [formData.items.length]);

  // run validation when parent indicates a submit attempt
  useEffect(() => {
    if (submitAttempted === undefined) return;
    const itemErrs = formData.items.map((it) => ({ id_barang: !it.id_barang, jumlah: !it.jumlah || it.jumlah < 1 }));
    const formErr = formData.items.length === 0;
    setErrors({ items: itemErrs, form: formErr });
  }, [submitAttempted]);



  return (
    <VStack gap={4} maxH="60vh" overflowY="auto" w="full" pr={2}>
      <Field.Root required>
        <SelectField
          label="Pelanggan"
          items={pelanggans.filter(p => p.id_pelanggan).map(p => ({ label: p.nama, value: p.id_pelanggan }))}
          value={formData.id_pelanggan}
          onChange={(v) => setFormData({ ...formData, id_pelanggan: v })}
          placeholder="Pilih pelanggan"
          required
          isInvalid={!!errors.form && !formData.id_pelanggan}
        />
      </Field.Root>

      <ScrollArea.Root variant="hover" maxH="40vh">
        <ScrollArea.Viewport>
          <VStack gap={4} align="stretch">
            {formData.items.map((item, idx) => (
              <Box key={idx} w="full" p={3} border="1px" borderColor="gray.200" rounded="md">
                <VStack gap={2}>
                  <Field.Root required>
                    <SelectField
                      label="Barang"
                      items={availableBarangs.map(b => ({ label: `${b.nama} - ${formatCurrency(b.harga_jual)}`, value: b.kode_barang }))}
                      value={item.id_barang}
                      onChange={(v) => {
                        const updated = [...formData.items];
                        updated[idx] = { ...updated[idx], id_barang: v };
                        setFormData({ ...formData, items: updated });
                      }}
                      placeholder="Pilih barang"
                      required
                      isInvalid={!!errors.items[idx]?.id_barang}
                    />
                  </Field.Root>
                  <Box w="full">
                    <NumberField
                      label="Jumlah"
                      value={item.jumlah}
                      min={1}
                      disabled={!item.id_barang}
                      onChange={(val) => {
                        const selectedBarang = barangs.find((b) => b.kode_barang === item.id_barang);
                        const max = selectedBarang ? (selectedBarang.stok ?? 0) : undefined;
                        let v = Math.max(1, Math.trunc(val || 1));

                        if (max !== undefined && v > max) {
                          v = max;
                          toaster.error({ title: 'Stok tidak cukup', description: `Stok tersedia ${max}` });
                        }

                        const updated = [...formData.items];
                        updated[idx] = { ...updated[idx], jumlah: v };
                        setFormData({ ...formData, items: updated });
                      }}
                      placeholder={!item.id_barang ? 'Pilih barang dulu' : undefined}
                      required
                      isInvalid={!!errors.items[idx]?.jumlah}
                    />
                    <Text fontSize="sm" color="gray.600">Stok: {barangs.find((b) => b.kode_barang === item.id_barang)?.stok ?? '-'}</Text>
                    {formData.items.length > 1 && (
                      <Button colorScheme="red" variant="outline" size="sm" mt={2}
                        onClick={() => setFormData({
                          ...formData,
                          items: formData.items.filter((_, i) => i !== idx),
                        })}>
                        Hapus
                      </Button>
                    )}
                  </Box>
                </VStack>
              </Box>
            ))}
          </VStack>
        </ScrollArea.Viewport>
        <ScrollArea.Scrollbar />
      </ScrollArea.Root>
      <Button variant="outline" onClick={() => setFormData({
        ...formData,
        items: [...formData.items, { id_barang: '', jumlah: 1 }],
      })} disabled={availableBarangs.length === 0} title={availableBarangs.length === 0 ? 'Tidak ada barang tersedia' : undefined}>
        + Item
      </Button>
    </VStack>
  );
}
