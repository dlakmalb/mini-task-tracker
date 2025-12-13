'use client';

import React from 'react';
import { Button, Form, Input, Select } from 'antd';
import type { UpsertTaskPayload } from '@/api/tasks';
import type { TaskPriority, TaskStatus } from '@/types';

type Props = {
  submitting: boolean;
  initialValues?: Partial<UpsertTaskPayload>;
  onCancel: () => void;
  onSubmit: (payload: UpsertTaskPayload) => Promise<void>;
};

const statusOptions: { label: string; value: TaskStatus }[] = [
  { label: 'Todo', value: 'todo' },
  { label: 'In Progress', value: 'in_progress' },
  { label: 'Done', value: 'done' },
];

const priorityOptions: { label: string; value: TaskPriority }[] = [
  { label: 'Low', value: 'low' },
  { label: 'Medium', value: 'medium' },
  { label: 'High', value: 'high' },
];

const TaskUpsertForm = ({ submitting, initialValues, onCancel, onSubmit }: Props) => {
  const [form] = Form.useForm<UpsertTaskPayload>();

  const handleFinish = async (values: UpsertTaskPayload) => {
    try {
      await onSubmit(values);
      form.resetFields();
    } catch {
      // keep the form values so the user can adjust and retry
    }
  };

  return (
    <Form
      form={form}
      layout="vertical"
      requiredMark={true}
      initialValues={{
        title: '',
        description: '',
        status: 'todo',
        priority: 'medium',
        ...initialValues,
      }}
      onFinish={handleFinish}
      preserve={false}
    >
      <Form.Item
        name="title"
        label="Task Title"
        rules={[
          { required: true, whitespace: true, message: 'Task title is required' },
          { min: 3, message: 'Title must be at least 3 characters' },
          { max: 120, message: 'Title must be 120 characters or less' },
        ]}
      >
        <Input placeholder="e.g. Fix pagination bug" autoFocus />
      </Form.Item>

      <Form.Item
        name="description"
        label="Description"
        rules={[{ max: 255, message: 'Description must be 255 characters or less' }]}
      >
        <Input.TextArea rows={3} placeholder="Optional" />
      </Form.Item>

      <Form.Item name="status" label="Status" rules={[{ required: true }]}>
        <Select options={statusOptions} />
      </Form.Item>

      <Form.Item name="priority" label="Priority" rules={[{ required: true }]}>
        <Select options={priorityOptions} />
      </Form.Item>

      <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
        <Button
          onClick={() => {
            form.resetFields();
            onCancel();
          }}
        >
          Cancel
        </Button>
        <Button type="primary" htmlType="submit" loading={submitting}>
          Save
        </Button>
      </div>
    </Form>
  );
};

export default TaskUpsertForm;
