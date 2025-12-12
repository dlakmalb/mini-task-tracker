'use client';

import ProjectsTable from '@/components/projects/ProjectsTable';
import { Button, message, Modal } from 'antd';
import { PlusCircleOutlined } from '@ant-design/icons';
import { useState } from 'react';
import { useProjects } from '@/hooks/useProjects';
import ProjectCreateForm from '@/components/projects/ProjectCreateForm';
import { createProject, type CreateProjectPayload } from '@/api/projects';

export default function ProjectsPage() {
  const [open, setOpen] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  const {
    projects,
    loading,
    error,
    page,
    pageSize,
    total,
    setPage,
    setPageSize,
    reload,
  } = useProjects(8);

  const handleCreate = async (payload: CreateProjectPayload) => {
    setSubmitting(true);
    try {
      await createProject(payload);
      message.success("Project created");
      setOpen(false);
      await reload();
    } catch (e) {
      message.error(e instanceof Error ? e.message : "Failed to create project");
      throw e;
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <>
      <div style={{ display: "flex", justifyContent: "flex-end", marginBottom: 12 }}>
        <Button
          type="default"
          icon={<PlusCircleOutlined />}
          onClick={() => setOpen(true)}
        >
          New
        </Button>
      </div>

      <ProjectsTable
        projects={projects}
        loading={loading}
        error={error}
        page={page}
        pageSize={pageSize}
        total={total}
        setPage={setPage}
        setPageSize={setPageSize}
      />

      <Modal
        title="Create Project"
        open={open}
        onCancel={() => setOpen(false)}
        footer={null}
        maskClosable={false}
        keyboard={false}
        destroyOnHidden
      >
        <ProjectCreateForm
          submitting={submitting}
          onCancel={() => setOpen(false)}
          onSubmit={handleCreate}
        />
      </Modal>
    </>
  );
}
