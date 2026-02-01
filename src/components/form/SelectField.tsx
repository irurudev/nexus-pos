import { useMemo, useRef, useEffect } from 'react';
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

  const posRef = useRef<HTMLDivElement | null>(null);

  // Accessibility: some browsers warn when an ancestor has aria-hidden while a focused
  // descendant remains focused. Observe the positioner and blur any focused element
  // inside it when aria-hidden becomes "true" to avoid the warning.
  useEffect(() => {
    const el = posRef.current;
    if (!el) return;

    const blurIfHidden = () => {
      const isHidden = el.getAttribute('aria-hidden') === 'true' || el.getAttribute('data-aria-hidden') === 'true';
      if (isHidden) {
        const active = document.activeElement as HTMLElement | null;
        if (active && el.contains(active)) {
          try { active.blur(); } catch (err) { /* ignore */ }
        }
      }
    };

    // Observe both aria-hidden and data-aria-hidden changes
    const obs = new MutationObserver((mutations) => {
      for (const m of mutations) {
        if (m.type === 'attributes' && (m.attributeName === 'aria-hidden' || m.attributeName === 'data-aria-hidden')) {
          blurIfHidden();
        }
      }
    });

    obs.observe(el, { attributes: true, attributeFilter: ['aria-hidden', 'data-aria-hidden'] });

    // Also handle the case where focus lands inside a hidden positioner (focusin)
    const onFocusIn = (ev: FocusEvent) => {
      const target = ev.target as HTMLElement | null;
      if (!target) return;
      const isHidden = el.getAttribute('aria-hidden') === 'true' || el.getAttribute('data-aria-hidden') === 'true';
      if (isHidden && el.contains(target)) {
        try { (target as HTMLElement).blur(); } catch (err) { /* ignore */ }
      }
    };

    document.addEventListener('focusin', onFocusIn);

    return () => {
      obs.disconnect();
      document.removeEventListener('focusin', onFocusIn);
    };
  }, []);

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
          <Select.Positioner ref={posRef}>
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
