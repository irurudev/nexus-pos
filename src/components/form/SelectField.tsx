import { useMemo } from 'react';
import { Field, Select, Portal, createListCollection, Text } from '@chakra-ui/react';
import { FiCheck } from 'react-icons/fi';

interface Item {
  label: string;
  value: string;
}

interface SelectFieldProps {
  label: string;
  items: Item[];
  value: string;
  onChange: (val: string) => void;
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
}

export default function SelectField({ label, items, value, onChange, placeholder, required, disabled }: SelectFieldProps) {
  const collection = useMemo(() => createListCollection({ items }), [items]);

  return (
    <Field.Root required={required}>
      <Field.Label color="gray.900">{label}</Field.Label>

      <Select.Root key={value || 'none'} collection={collection}>
        <Select.HiddenSelect value={value} onChange={(e: React.ChangeEvent<HTMLSelectElement>) => onChange(e.target.value)} disabled={disabled} />

        <Select.Control>
          <Select.Trigger color="gray.900">
            <Text color="gray.900">{collection.items.find(it => it.value === value)?.label || placeholder}</Text>
          </Select.Trigger>
          <Select.IndicatorGroup>
            <Select.Indicator />
          </Select.IndicatorGroup>
        </Select.Control>

        <Portal>
          <Select.Positioner>
            <Select.Content zIndex="overlay">
              {collection.items.map((it) => (
                <Select.Item key={it.value} item={it} _hover={{ bg: 'gray.50' }} _focus={{ bg: 'gray.50' }} px={3} py={2}>
                  <Text color="gray.900">{it.label}</Text>
                  <Select.ItemIndicator>
                    <FiCheck />
                  </Select.ItemIndicator>
                </Select.Item>
              ))}
            </Select.Content>
          </Select.Positioner>
        </Portal>
      </Select.Root>
    </Field.Root>
  );
}
