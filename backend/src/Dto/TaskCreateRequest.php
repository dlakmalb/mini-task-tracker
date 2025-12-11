<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TaskCreateRequest
{
    #[Assert\NotBlank(message: 'Title is required.')]
    public ?string $title = null;

    public ?string $description = null;

    // status is optional (default = todo)
    public ?string $status = null;

    #[Assert\NotBlank(message: 'Priority is required.')]
    public ?string $priority = null;
}
