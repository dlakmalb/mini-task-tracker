<?php

namespace App\Service;

use App\Enum\TaskStatus;

final class TaskStatusTransitionService
{
    public function canTransition(TaskStatus $from, TaskStatus $to): bool
    {
        return match ($from) {
            TaskStatus::TODO => in_array($to, [TaskStatus::IN_PROGRESS, TaskStatus::DONE], true),
            TaskStatus::IN_PROGRESS => TaskStatus::DONE === $to,
            TaskStatus::DONE => false,
        };
    }

    public function isFastTracked(TaskStatus $from, TaskStatus $to): bool
    {
        // todo -> done is allowed but "fasttracked"
        return TaskStatus::TODO === $from && TaskStatus::DONE === $to;
    }
}
