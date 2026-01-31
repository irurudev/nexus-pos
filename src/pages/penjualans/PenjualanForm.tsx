import {  } from 'react';
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

export default function PenjualanForm({ barangs, pelanggans, formData, setFormData }: PenjualanFormProps) {const toaster = createToaster({ placement: 'top-end', pauseOnPageIdle: true });
  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(value);
  };



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
                      items={barangs.filter(b => b.kode_barang).map(b => ({ label: `${b.nama} - ${formatCurrency(b.harga_jual)}`, value: b.kode_barang }))}
                      value={item.id_barang}
                      onChange={(v) => {
                        const updated = [...formData.items];
                        updated[idx] = { ...updated[idx], id_barang: v };
                        setFormData({ ...formData, items: updated });
                      }}
                      placeholder="Pilih barang"
                      required
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
      })}>
        + Item
      </Button>
    </VStack>
  );
}
