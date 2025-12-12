<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\Request;

final class PaginationHelper
{
    public static function fromRequest(
        Request $request,
        int $defaultLimit = 10,
        int $maxLimit = 50,
    ): array {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', $defaultLimit);

        // normalize values
        $page = max(1, $page);
        $limit = max(1, min($maxLimit, $limit));

        return [$page, $limit];
    }
}
