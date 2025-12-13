'use client';

import { Button, Input, Select } from 'antd';
import { ArrowLeftOutlined, PlusCircleOutlined } from '@ant-design/icons';
import { TaskStatus } from '@/types';

type Props = {
  onCreate: () => void;
  search: string;
  status: TaskStatus | null;
  onSearchChange: (v: string) => void;
  onStatusChange: (s: TaskStatus | null) => void;
  onBack: () => void;
};

const statusOptions: { value: TaskStatus; label: string }[] = [
  { value: 'todo', label: 'Todo' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'done', label: 'Done' },
];

const TasksHeaderBar = ({
  onCreate,
  search,
  status,
  onSearchChange,
  onStatusChange,
  onBack,
}: Props) => {
  return (
    <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 12 }}>
      <div style={{ display: 'flex', gap: 10 }}>
        <Button icon={<ArrowLeftOutlined />} onClick={onBack}></Button>
        <Select<TaskStatus>
          value={status ?? undefined}
          placeholder="Status"
          allowClear
          options={statusOptions}
          style={{ width: 160 }}
          onChange={(value: TaskStatus) => onStatusChange(value ?? null)}
        />
      </div>

      <Input
        value={search}
        placeholder="Search tasks..."
        allowClear
        onChange={(e) => onSearchChange(e.target.value)}
        style={{ width: 400 }}
      />

      <Button type="default" icon={<PlusCircleOutlined />} onClick={onCreate}>
        New
      </Button>
    </div>
  );
};

export default TasksHeaderBar;
