'use client';

import React, { useMemo } from 'react';
import { Skeleton, Table, Typography } from 'antd';
import type { ColumnsType } from 'antd/es/table';
import { useRouter } from 'next/navigation';

import { useProjects } from '@/hooks/useProjects';
import type { Project } from '@/types';
import styles from './ProjectsTable.module.css';

const formatDateTime = (value?: string) => {
  if (!value) return '-';

  return new Date(value).toLocaleString(undefined, {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
};

export default function ProjectsTable() {
  const router = useRouter();

  const { projects, loading, error, page, pageSize, total, setPage, setPageSize } = useProjects(8);

  const columns: ColumnsType<Project> = useMemo(
    () => [
      {
        title: 'Project Name',
        dataIndex: 'name',
        key: 'name',
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
        title: 'Created At',
        dataIndex: 'createdAt',
        key: 'createdAt',
        width: 220,
        render: (value?: string) => (
          <Typography.Text type="secondary">{formatDateTime(value)}</Typography.Text>
        ),
      },
    ],
    [],
  );

  if (loading) {
    return <Skeleton active paragraph={{ rows: 6 }} />;
  }

  return (
    <>
      {error && (
        <Typography.Text type="danger" style={{ display: 'block', marginBottom: 12 }}>
          {error}
        </Typography.Text>
      )}

      <Table<Project>
        rowKey="id"
        className={styles.table}
        loading={false}
        columns={columns}
        dataSource={projects}
        pagination={{
          current: page,
          pageSize,
          total,
          pageSizeOptions: [5, 10, 20, 50],
          onChange: (nextPage, nextPageSize) => {
            setPage(nextPage);
            if (nextPageSize !== pageSize) setPageSize(nextPageSize);
          },
        }}
        onRow={(record) => ({
          onClick: () => router.push(`/projects/${record.id}/tasks`),
          className: styles.row,
        })}
      />
    </>
  );
}
