<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateProjectRequestDTO
{
    #[Assert\NotBlank(message: 'Project name is required.')]
    public ?string $name = null;

    public ?string $description = null;
}
