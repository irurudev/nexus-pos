import { HStack, Button, Text } from '@chakra-ui/react';

interface PaginationProps {
  page: number;
  setPage: (p: number) => void;
  lastPage: number;
  total?: number;
  pageSize?: number;
}

export default function Pagination({ page, setPage, lastPage, total, pageSize = 10 }: PaginationProps) {
  const pages = Array.from({ length: lastPage }, (_, i) => i + 1);

  return (
    <HStack justify="space-between" mt={4} alignItems="center">
      <Text color="gray.600">Menampilkan {(page - 1) * pageSize + 1} - {Math.min(page * pageSize, total ?? page * pageSize)}{total ? ` dari ${total}` : ''}</Text>

      <HStack>
        <Button size="sm" onClick={() => setPage(1)} disabled={page === 1}>First</Button>
        <Button size="sm" onClick={() => setPage(Math.max(1, page - 1))} disabled={page === 1}>Prev</Button>

        {pages.map(p => (
          <Button key={p} size="sm" variant={p === page ? 'solid' : 'ghost'} colorScheme={p === page ? 'brand' : undefined} onClick={() => setPage(p)}>{p}</Button>
        ))}

        <Button size="sm" onClick={() => setPage(Math.min(lastPage, page + 1))} disabled={page === lastPage}>Next</Button>
        <Button size="sm" onClick={() => setPage(lastPage)} disabled={page === lastPage}>Last</Button>
      </HStack>
    </HStack>
  );
}
