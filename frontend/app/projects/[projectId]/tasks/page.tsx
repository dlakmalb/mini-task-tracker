type Props = {
  params: Promise<{
    projectId: string;
  }>;
};

const ProjectTasksPage = async ({ params }: Props) => {
  const { projectId } = await params;

  return (
    <div>
      <h2 style={{ marginTop: 0 }}>Tasks</h2>

      <p>
        Project ID: <strong>{projectId}</strong>
      </p>

      <p>This is a placeholder page for project tasks.</p>
    </div>
  );
};

export default ProjectTasksPage;
