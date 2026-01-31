import { Button, DialogRoot, DialogContent, DialogHeader, DialogTitle, DialogBody, DialogFooter, DialogBackdrop, DialogCloseTrigger } from '@chakra-ui/react';
import type { ReactNode } from 'react';

interface ConfirmDialogProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  description?: ReactNode;
  confirmLabel?: string;
  cancelLabel?: string;
  onConfirm?: () => Promise<void> | void;
  confirmColorScheme?: string;
  isLoading?: boolean;
}

export default function ConfirmDialog({ isOpen, onClose, title = 'Konfirmasi', description, confirmLabel = 'Hapus', cancelLabel = 'Batal', onConfirm, confirmColorScheme = 'red', isLoading = false }: ConfirmDialogProps) {
  const handleConfirm = async () => {
    try {
      await onConfirm?.();
    } finally {
      onClose();
    }
  };

  return (
    <DialogRoot open={isOpen} onOpenChange={(v: any) => {
      const open = typeof v === 'object' ? v.open : v;
      if (!open) onClose();
    }}>
      <DialogBackdrop />
      <DialogContent position="fixed" top={{ base: '20vh', md: '25vh' }} left="50%" transform="translateX(-50%)" zIndex="overlay" maxW={{ base: '90vw', md: '520px' }}>
        <DialogHeader>
          <DialogTitle color="gray.900">{title}</DialogTitle>
          <DialogCloseTrigger />
        </DialogHeader>
        <DialogBody color="gray.900">{description}</DialogBody>
        <DialogFooter>
          <Button variant="ghost" onClick={onClose}> {cancelLabel} </Button>
          <Button colorScheme={confirmColorScheme} onClick={handleConfirm} loading={isLoading}>{confirmLabel}</Button>
        </DialogFooter>
      </DialogContent>
    </DialogRoot>
  );
}
