import type { Task, TaskPriority, TaskStatus } from '@/types';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api';

export type UpsertTaskPayload = {
  title: string;
  description?: string | null;
  status: TaskStatus;
  priority: TaskPriority;
};

export type PaginatedTasksResponse = {
  data: Task[];
  total: number;
  page: number;
  limit: number;
};

export const getTasks = async (
  projectId: string,
  page = 1,
  limit = 10,
  title?: string,
  status?: TaskStatus | null,
): Promise<PaginatedTasksResponse> => {
  const url = new URL(`${API_BASE_URL}/projects/${projectId}/tasks`);

  url.searchParams.set('page', String(page));
  url.searchParams.set('limit', String(limit));

  if (title && title.trim()) {
    url.searchParams.set('title', title.trim());
  }

  if (status) url.searchParams.set('status', status);

  const res = await fetch(url.toString(), {
    headers: { Accept: 'application/json' },
    cache: 'no-store',
  });

  if (!res.ok) {
    let message = 'Failed to load tasks';

    try {
      const body = await res.json();

      if (body?.message) message = body.message;
    } catch {
      /* ignore */
    }

    throw new Error(message);
  }

  return res.json();
};

export const createTask = async (projectId: string, payload: UpsertTaskPayload) => {
  const url = new URL(`${API_BASE_URL}/projects/${projectId}/tasks`);

  const res = await fetch(url.toString(), {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
    cache: 'no-store',
  });

  if (!res.ok) {
    let message = 'Failed to create task';

    try {
      const body = await res.json();

      if (body?.message) message = body.message;
    } catch {
      /* ignore */
    }

    throw new Error(message);
  }

  return res.json();
};

export const updateTask = async (
  projectId: string,
  taskId: string | number,
  payload: UpsertTaskPayload,
) => {
  const url = new URL(`${API_BASE_URL}/projects/${projectId}/tasks/${taskId}`);

  const res = await fetch(url.toString(), {
    method: 'PATCH',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
    cache: 'no-store',
  });

  if (!res.ok) {
    let message = 'Failed to update task';

    try {
      const body = await res.json();

      if (body?.error) message = body.error;
    } catch {
      /* ignore */
    }

    throw new Error(message);
  }

  return res.json();
};

export const deleteTask = async (projectId: string, taskId: string | number) => {
  const url = new URL(`${API_BASE_URL}/projects/${projectId}/tasks/${taskId}`);

  const res = await fetch(url.toString(), {
    method: 'DELETE',
    headers: { Accept: 'application/json' },
    cache: 'no-store',
  });

  if (!res.ok) {
    let message = 'Failed to delete task';

    try {
      const body = await res.json();

      if (body?.message) message = body.message;
      if (body?.error) message = body.error;
    } catch {
      /* ignore */
    }

    throw new Error(message);
  }
};
