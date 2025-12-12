export type Project = {
  id: number;
  name: string;
  description?: string | null;
  createdAt: string;
};

export type TaskStatus = 'todo' | 'in_progress' | 'done';
export type TaskPriority = 'low' | 'medium' | 'high';

export type Task = {
  id: number;
  projecId: number;
  title: string;
  description?: string | null;
  status: TaskStatus;
  priority: TaskPriority;
  createdAt: string;
};
