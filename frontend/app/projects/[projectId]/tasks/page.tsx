import TasksClient from '@/components/tasks/TasksClient';

type Props = { params: Promise<{ projectId: string }> };

const ProjectTasksPage = async ({ params }: Props) => {
  const { projectId } = await params;

  return <TasksClient projectId={projectId} />;
};

export default ProjectTasksPage;
