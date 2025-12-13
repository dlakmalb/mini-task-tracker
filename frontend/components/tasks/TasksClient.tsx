'use client';

import { createTask, deleteTask, updateTask, UpsertTaskPayload } from '@/api/tasks';
import TasksHeaderBar from '@/components/tasks/TasksHeaderBar';
import TasksTable from '@/components/tasks/TasksTable';
import { useTasks } from '@/hooks/useTasks';
import { Task } from '@/types';
import { message } from 'antd';
import { useState } from 'react';
import TaskUpsertModal from './TaskUpsertModal';
import { useRouter } from 'next/navigation';

type Props = {
  projectId: string;
};

const TasksClient = ({ projectId }: Props) => {
  const router = useRouter();

  const {
    tasks,
    loading,
    error,
    page,
    pageSize,
    total,
    setPage,
    setPageSize,
    reload,
    title,
    status,
    setTitle,
    setStatus,
  } = useTasks(projectId, 8);

  const [open, setOpen] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [editingTask, setEditingTask] = useState<Task | null>(null);
  const [deletingId, setDeletingId] = useState<string | number | null>(null);

  const closeModal = () => {
    setOpen(false);
    setEditingTask(null);
  };

  const openCreate = () => {
    setEditingTask(null);
    setOpen(true);
  };

  const openEdit = (task: Task) => {
    setEditingTask(task);
    setOpen(true);
  };

  const handleSubmit = async (payload: UpsertTaskPayload) => {
    setSubmitting(true);

    try {
      if (editingTask) {
        await updateTask(projectId, editingTask.id, payload);
        message.success('Task updated');
      } else {
        await createTask(projectId, payload);
        message.success('Task created');
      }

      closeModal();
      await reload();
    } catch (e) {
      message.error(e instanceof Error ? e.message : 'Failed to save task');
      throw e; // Avoid form reset on error
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (task: Task) => {
    setDeletingId(task.id);
    try {
      await deleteTask(projectId, task.id);
      message.success('Task deleted');
      await reload();
    } catch (e) {
      message.error(e instanceof Error ? e.message : 'Failed to delete task');
    } finally {
      setDeletingId(null);
    }
  };

  return (
    <>
      <TasksHeaderBar
        onCreate={openCreate}
        search={title}
        status={status}
        onSearchChange={setTitle}
        onStatusChange={setStatus}
        onBack={() => router.push('/projects')}
      />

      <TasksTable
        tasks={tasks}
        loading={loading}
        error={error}
        page={page}
        pageSize={pageSize}
        total={total}
        setPage={setPage}
        setPageSize={setPageSize}
        onEdit={openEdit}
        onDelete={handleDelete}
        deletingId={deletingId}
      />

      <TaskUpsertModal
        open={open}
        submitting={submitting}
        title={editingTask ? 'Update Task' : 'Create Task'}
        onClose={closeModal}
        onSubmit={handleSubmit}
        initialValues={
          editingTask
            ? {
                title: editingTask.title,
                description: editingTask.description,
                status: editingTask.status,
                priority: editingTask.priority,
              }
            : undefined
        }
      />
    </>
  );
};

export default TasksClient;
