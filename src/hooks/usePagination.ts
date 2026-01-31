import { useMemo } from 'react';

export default function usePagination({ page, pageSize, total, lastPage }: { page: number; pageSize?: number; total?: number; lastPage: number }) {
  const pageSizeActual = pageSize ?? 10;

  const pageStart = useMemo(() => (page - 1) * pageSizeActual + 1, [page, pageSizeActual]);
  const pageEnd = useMemo(() => Math.min(page * pageSizeActual, total ?? page * pageSizeActual), [page, pageSizeActual, total]);

  return { pageStart, pageEnd, pageSize: pageSizeActual, total: total ?? pageSizeActual * lastPage, lastPage };
}
