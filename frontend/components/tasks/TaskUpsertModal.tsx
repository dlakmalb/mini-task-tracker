'use client';

import React from 'react';
import { Modal } from 'antd';
import TaskUpsertForm from '@/components/tasks/TaskUpsertForm';
import type { UpsertTaskPayload } from '@/api/tasks';

type Props = {
  open: boolean;
  submitting: boolean;
  title: string;
  onClose: () => void;
  onSubmit: (payload: UpsertTaskPayload) => Promise<void>;
  initialValues?: Partial<UpsertTaskPayload>;
};

const TaskUpsertModal = ({ open, submitting, title, onClose, onSubmit, initialValues }: Props) => {
  return (
    <Modal
      title={title}
      open={open}
      onCancel={onClose}
      footer={null}
      maskClosable={false}
      keyboard={false}
      destroyOnHidden
    >
      <TaskUpsertForm
        submitting={submitting}
        onCancel={onClose}
        onSubmit={onSubmit}
        initialValues={initialValues}
      />
    </Modal>
  );
};

export default TaskUpsertModal;
