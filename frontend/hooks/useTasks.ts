'use client';

import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import type { Task, TaskStatus } from '@/types';
import { getTasks, PaginatedTasksResponse } from '@/api/tasks';

type UseTasksResult = {
  tasks: Task[];
  loading: boolean;
  error: string | null;

  page: number;
  pageSize: number;
  total: number;
  pageCount: number;
  title: string;
  status: TaskStatus | null;

  setTitle: (title: string) => void;
  setStatus: (status: TaskStatus | null) => void;
  setPage: (page: number) => void;
  setPageSize: (size: number) => void;
  reload: () => Promise<void>;
};

const useDebouncedValue = <T>(value: T, delay = 350) => {
  const [debounced, setDebounced] = useState(value);

  useEffect(() => {
    const id = setTimeout(() => setDebounced(value), delay);
    return () => clearTimeout(id);
  }, [value, delay]);

  return debounced;
};

export const useTasks = (projectId: string, initialPageSize = 10): UseTasksResult => {
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [page, setPage] = useState(1);
  const [pageSize, setPageSize] = useState(initialPageSize);
  const [total, setTotal] = useState(0);

  const [title, setTitle] = useState('');
  const debouncedTitle = useDebouncedValue(title, 350);

  const [status, setStatus] = useState<TaskStatus | null>(null);

  const inFlightKeyRef = useRef<string | null>(null);

  const load = useCallback(
    async (page: number, pageSize: number, searchTitle: string, status: TaskStatus | null) => {
      if (!projectId) return;

      const key = `${page}:${pageSize}`;

      if (inFlightKeyRef.current === key) return;
      inFlightKeyRef.current = key;

      try {
        setLoading(true);
        setError(null);

        const data: PaginatedTasksResponse = await getTasks(
          projectId,
          page,
          pageSize,
          searchTitle,
          status,
        );

        setTasks(data.data);
        setTotal(data.total);
        setPage(data.page);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error');
      } finally {
        setLoading(false);
        inFlightKeyRef.current = null;
      }
    },
    [projectId],
  );

  useEffect(() => setPage(1), [projectId]);
  useEffect(() => setPage(1), [debouncedTitle, status]);

  useEffect(() => {
    void load(page, pageSize, debouncedTitle, status);
  }, [page, pageSize, debouncedTitle, load, status]);

  const setPageSizeSafe = (size: number) => {
    setPage(1);
    setPageSize(size);
  };

  const pageCount = useMemo(() => (total > 0 ? Math.ceil(total / pageSize) : 1), [total, pageSize]);

  return {
    tasks,
    loading,
    error,
    page,
    pageSize,
    total,
    pageCount,
    title,
    status,
    setPage,
    setPageSize: setPageSizeSafe,
    setTitle,
    setStatus,
    reload: () => load(page, pageSize, debouncedTitle, status),
  };
};
