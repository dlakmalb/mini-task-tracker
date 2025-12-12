'use client';

import { useCallback, useEffect, useMemo, useState } from 'react';
import type { Project } from '@/types';
import { getProjects } from '@/api/projects';

type UseProjectsResult = {
  projects: Project[];
  loading: boolean;
  error: string | null;

  page: number;
  pageSize: number;
  total: number;
  pageCount: number;

  setPage: (page: number) => void;
  setPageSize: (size: number) => void;
  reload: () => Promise<void>;
};

export const useProjects = (initialPageSize = 10): UseProjectsResult => {
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [page, setPage] = useState(1);
  const [pageSize, setPageSize] = useState(initialPageSize);
  const [total, setTotal] = useState(0);

  const load = useCallback(async (p: number, ps: number) => {
    try {
      setLoading(true);
      setError(null);

      const data = await getProjects(p, ps);

      setProjects(data.data);
      setTotal(data.total);
      setPage(data.page);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unknown error');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void load(page, pageSize);
  }, [page, pageSize, load]);

  const setPageSizeSafe = (size: number) => {
    setPage(1);
    setPageSize(size);
  };

  const pageCount = useMemo(() => (total > 0 ? Math.ceil(total / pageSize) : 1), [total, pageSize]);

  return {
    projects,
    loading,
    error,
    page,
    pageSize,
    total,
    pageCount,
    setPage,
    setPageSize: setPageSizeSafe,
    reload: () => load(page, pageSize),
  };
};
