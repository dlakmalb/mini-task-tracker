import type { Project } from '@/types';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api';

export type CreateProjectPayload = {
  name: string;
  description?: string;
};

export type PaginatedProjectsResponse = {
  data: Project[];
  total: number;
  page: number;
  limit: number;
};

export const getProjects = async (page = 1, limit = 10): Promise<PaginatedProjectsResponse> => {
  const url = new URL(`${API_BASE_URL}/projects`);

  url.searchParams.set('page', String(page));
  url.searchParams.set('limit', String(limit));

  const res = await fetch(url.toString(), {
    headers: { Accept: 'application/json' },
    cache: 'no-store',
  });

  if (!res.ok) {
    let message = 'Failed to load projects';
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

export const createProject = async (payload: CreateProjectPayload) => {
  const url = new URL(`${API_BASE_URL}/projects`);

  const res = await fetch(url.toString(), {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });

  if (!res.ok) {
    let message = 'Failed to create project';
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
