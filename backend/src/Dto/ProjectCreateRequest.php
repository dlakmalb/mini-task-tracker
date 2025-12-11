<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProjectCreateRequest
{
    #[Assert\NotBlank(message: 'Project name is required.')]
    public ?string $name = null;

    public ?string $description = null;
}
