'use client';

import React, { useMemo } from 'react';
import { Table, Typography } from 'antd';
import type { ColumnsType } from 'antd/es/table';
import { useRouter } from 'next/navigation';

import type { Project } from '@/types';
import styles from '../shared/TableStyles.module.css';
import { formatDateTime } from '@/app/utils/date';

type Props = {
  projects: Project[];
  loading: boolean;
  error: string | null;
  page: number;
  pageSize: number;
  total: number;
  setPage: (page: number) => void;
  setPageSize: (size: number) => void;
};

const ProjectsTable = ({
  projects,
  loading,
  error,
  page,
  pageSize,
  total,
  setPage,
  setPageSize,
}: Props) => {
  const router = useRouter();

  const columns: ColumnsType<Project> = useMemo(
    () => [
      {
        title: 'Project Name',
        dataIndex: 'name',
        key: 'name',
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
        loading={loading}
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
};

export default ProjectsTable;
