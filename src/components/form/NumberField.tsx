import { Field, NumberInput } from '@chakra-ui/react';

interface NumberFieldProps {
  label: string;
  value?: number;
  onChange: (val: number) => void;
  min?: number;
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
}

export default function NumberField({ label, value, onChange, min = 0, placeholder, required, disabled }: NumberFieldProps) {
  return (
    <Field.Root required={required}>
      <Field.Label color="gray.900">{label}</Field.Label>
      <NumberInput.Root min={min} value={value !== undefined ? String(value) : undefined} onValueChange={(details) => onChange(Math.trunc(details.valueAsNumber || 0))}>
        <NumberInput.Control />
        <NumberInput.Input color="gray.900" placeholder={placeholder} disabled={disabled} />
      </NumberInput.Root>
    </Field.Root>
  );
}
