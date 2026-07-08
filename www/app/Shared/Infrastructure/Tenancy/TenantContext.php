<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Tenancy;

final class TenantContext
{
    private ?int $companyId = null;

    public function setCompanyId(int $companyId): void
    {
        $this->companyId = $companyId;
    }

    public function companyId(): ?int
    {
        return $this->companyId;
    }

    public function hasTenant(): bool
    {
        return $this->companyId !== null;
    }

    public function clear(): void
    {
        $this->companyId = null;
    }
}
