'use client';

import React from 'react';
import { Button, Form, Input } from 'antd';
import type { CreateProjectPayload } from '@/api/projects';

type Props = {
  onSubmit: (payload: CreateProjectPayload) => Promise<void>;
  onCancel: () => void;
  submitting: boolean;
};

const ProjectCreateForm = ({ onSubmit, onCancel, submitting }: Props) => {
  const [form] = Form.useForm<CreateProjectPayload>();

  const handleFinish = async (values: CreateProjectPayload) => {
    try {
      await onSubmit(values);
      form.resetFields();
    } catch {
      // keep the form values so the user can adjust and retry
    }
  };

  return (
    <Form form={form} layout="vertical" onFinish={handleFinish} requiredMark={true}>
      <Form.Item
        name="name"
        label="Project Name"
        rules={[
          { required: true, whitespace: true, message: 'Project name is required' },
          { min: 3, message: 'Project name must be at least 3 characters' },
          { max: 80, message: 'Project name must be 80 characters or less' },
        ]}
      >
        <Input placeholder="e.g. Mini Task Tracker" autoFocus />
      </Form.Item>

      <Form.Item
        name="description"
        label="Description"
        rules={[{ max: 255, message: 'Description must be 255 characters or less' }]}
      >
        <Input.TextArea rows={3} placeholder="Optional" />
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
          Create
        </Button>
      </div>
    </Form>
  );
};

export default ProjectCreateForm;
