<?php

declare(strict_types=1);

namespace App\Core\Install;

final readonly class InstallOptions
{
    public function __construct(
        public ?string $adminEmail = null,
        public ?string $adminPassword = null,
        public bool $withStarterSite = false,
        public ?string $siteTitle = null,
        public bool $allowWeakPassword = false,
    ) {}
}
