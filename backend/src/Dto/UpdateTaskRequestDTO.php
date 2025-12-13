<?php

namespace App\DTO;

class UpdateTaskRequestDTO
{
    public ?string $title = null;
    public ?string $description = null;
    public ?string $status = null;
    public ?string $priority = null;
}
