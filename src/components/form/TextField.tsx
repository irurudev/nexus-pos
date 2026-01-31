import { Field, Input } from '@chakra-ui/react';

interface TextFieldProps {
  label: string;
  value: string | undefined;
  onChange: (val: string) => void;
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
}

export default function TextField({ label, value, onChange, placeholder, required, disabled }: TextFieldProps) {
  return (
    <Field.Root required={required}>
      <Field.Label color="gray.900">{label}</Field.Label>
      <Input placeholder={placeholder} value={value} onChange={(e) => onChange(e.target.value)} disabled={disabled} color="gray.900" />
    </Field.Root>
  );
}
