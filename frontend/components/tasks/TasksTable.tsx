'use client';

import React, { useMemo } from 'react';
import { Table, Typography, Tag, TagProps, Popconfirm, Button } from 'antd';
import type { ColumnsType } from 'antd/es/table';
import {
  CheckCircleOutlined,
  ClockCircleOutlined,
  DeleteOutlined,
  SyncOutlined,
} from '@ant-design/icons';

import { Task, TaskPriority, TaskStatus } from '@/types';
import styles from '../shared/TableStyles.module.css';
import { formatDateTime } from '@/app/utils/date';

type Props = {
  tasks: Task[];
  loading: boolean;
  error: string | null;
  page: number;
  pageSize: number;
  total: number;
  setPage: (page: number) => void;
  setPageSize: (size: number) => void;
  onEdit: (task: Task) => void;
  onDelete: (task: Task) => void;
  deletingId?: number | string | null;
};

type TagConfig = {
  color: TagProps['color'];
  label: string;
  icon?: React.ReactNode;
  variant?: TagProps['variant'];
};

const statusTagConfig: Record<TaskStatus, TagConfig> = {
  todo: { color: 'default', label: 'TODO', icon: <ClockCircleOutlined /> },
  in_progress: { color: 'processing', label: 'IN PROGRESS', icon: <SyncOutlined spin /> },
  done: { color: 'success', label: 'DONE', icon: <CheckCircleOutlined /> },
};

const priorityTagConfig: Record<TaskPriority, TagConfig> = {
  low: { color: 'magenta', label: 'LOW', variant: 'solid' },
  medium: { color: 'green', label: 'MEDIUM', variant: 'solid' },
  high: { color: 'red', label: 'HIGH', variant: 'solid' },
};

const ConfigTag = ({ cfg }: { cfg: TagConfig }) => (
  <Tag color={cfg.color} icon={cfg.icon} variant={cfg.variant}>
    {cfg.label}
  </Tag>
);

const TasksTable = ({
  tasks,
  loading,
  error,
  page,
  pageSize,
  total,
  setPage,
  setPageSize,
  onEdit,
  onDelete,
  deletingId,
}: Props) => {
  const columns: ColumnsType<Task> = useMemo(
    () => [
      {
        title: 'Title',
        dataIndex: 'title',
        key: 'title',
        ellipsis: true,
        render: (value: string) => <Typography.Text strong>{value}</Typography.Text>,
      },
      {
        title: 'Description',
        dataIndex: 'description',
        key: 'description',
        ellipsis: true,
        render: (value?: string) =>
          value ? value : <Typography.Text type="secondary">-</Typography.Text>,
      },
      {
        title: 'Status',
        dataIndex: 'status',
        key: 'status',
        width: 160,
        render: (status: TaskStatus) => <ConfigTag cfg={statusTagConfig[status]} />,
        filters: [
          {
            text: 'Todo',
            value: 'todo',
          },
          {
            text: 'In Progress',
            value: 'in_progress',
          },
          {
            text: 'Done',
            value: 'done',
          },
        ],
        onFilter: (value, record) => record.status.indexOf(value as string) === 0,
      },
      {
        title: 'Priority',
        dataIndex: 'priority',
        key: 'priority',
        width: 140,
        render: (priority: TaskPriority) => <ConfigTag cfg={priorityTagConfig[priority]} />,
      },
      {
        title: 'Created At',
        dataIndex: 'createdAt',
        key: 'createdAt',
        width: 220,
        render: (value: string) => (
          <Typography.Text type="secondary">{formatDateTime(value)}</Typography.Text>
        ),
      },
      {
        title: '',
        key: 'actions',
        width: 70,
        render: (_: unknown, record: Task) => (
          <Popconfirm
            title="Delete task?"
            description="This cannot be undone."
            okText="Delete"
            cancelText="Cancel"
            okButtonProps={{ danger: true }}
            onConfirm={(e) => {
              e?.stopPropagation?.();
              onDelete(record);
            }}
            onCancel={(e) => e?.stopPropagation?.()}
          >
            <Button
              type="text"
              danger
              icon={<DeleteOutlined />}
              loading={deletingId === record.id}
              onClick={(e) => e.stopPropagation()}
              onMouseDown={(e) => e.stopPropagation()}
            />
          </Popconfirm>
        ),
      },
    ],
    [deletingId, onDelete],
  );

  return (
    <>
      {error && (
        <Typography.Text type="danger" style={{ display: 'block', marginBottom: 12 }}>
          {error}
        </Typography.Text>
      )}

      <Table<Task>
        rowKey="id"
        size="middle"
        className={styles.table}
        loading={loading}
        columns={columns}
        dataSource={tasks}
        pagination={{
          current: page,
          pageSize,
          total,
          showSizeChanger: true,
          pageSizeOptions: [5, 10, 20, 50],
          onChange: (nextPage, nextPageSize) => {
            setPage(nextPage);
            if (nextPageSize !== pageSize) setPageSize(nextPageSize);
          },
        }}
        onRow={(record) => ({
          onClick: () => onEdit(record),
          className: styles.row,
        })}
      />
    </>
  );
};

export default TasksTable;
