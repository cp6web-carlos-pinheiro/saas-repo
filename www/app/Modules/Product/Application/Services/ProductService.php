<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\Services;

use App\Modules\Product\Application\DTO\CreateProductDTO;
use App\Modules\Product\Application\DTO\UpdateProductDTO;
use App\Modules\Product\Domain\Repositories\ProductRepository;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ProductService extends BaseService
{
    private const VALID_TYPES = ['FG', 'WIP', 'RAW', 'CONSUMABLE'];

    public function __construct(
        private readonly ProductRepository $repository,
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    )
    {
        parent::__construct($transaction, $cache, $logger);
    }

    public function paginate(int $perPage = 15, array $filters = [], ?string $sortBy = null, string $sortDirection = 'asc'): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filters, $sortBy, $sortDirection);
    }

    public function create(CreateProductDTO $dto): array
    {
        $this->assertType($dto->product_type);
        $this->assertSkuAvailable($dto->sku);

        $product = $this->inTransaction(function () use ($dto) {
            return $this->repository->create($this->resolveUnitPayload($dto->toArray()));
        });

        $this->logger->info('product.created', [
            'product_id' => $product->id,
            'sku' => $product->sku,
        ]);

        return $product->toArray();
    }

    public function update(int $id, UpdateProductDTO $dto): array
    {
        $this->assertType($dto->product_type);

        $product = $this->inTransaction(function () use ($id, $dto) {
            return $this->repository->update($id, $this->resolveUnitPayload($dto->toArray()));
        });

        $this->logger->info('product.updated', [
            'product_id' => $product->id,
            'sku' => $product->sku,
        ]);

        return $product->toArray();
    }

    public function find(int $id): array
    {
        return $this->repository->findOrFail($id)->toArray();
    }

    public function delete(int $id): void
    {
        $product = $this->repository->findOrFail($id);

        $this->inTransaction(function () use ($id): void {
            $this->repository->delete($id);
        });

        $this->logger->info('product.deleted', [
            'product_id' => $id,
            'sku' => $product->sku,
        ]);
    }

    public function bulkCreate(array $items): array
    {
        if (empty($items)) {
            throw new DomainException('Bulk create payload cannot be empty', 422);
        }

        $created = $this->inTransaction(function () use ($items): array {
            $rows = [];

            foreach ($items as $item) {
                $dto = CreateProductDTO::fromArray($item);
                $this->assertType($dto->product_type);
                $this->assertSkuAvailable($dto->sku);
                $rows[] = $this->repository->create($this->resolveUnitPayload($dto->toArray()))->toArray();
            }

            return $rows;
        });

        return [
            'count' => count($created),
            'items' => $created,
        ];
    }

    public function bulkUpdate(array $items): array
    {
        if (empty($items)) {
            throw new DomainException('Bulk update payload cannot be empty', 422);
        }

        $updated = $this->inTransaction(function () use ($items): array {
            $rows = [];

            foreach ($items as $item) {
                $id = (int) $item['id'];
                $dto = UpdateProductDTO::fromArray($item);
                $this->assertType($dto->product_type);

                $rows[] = $this->repository->update($id, $this->resolveUnitPayload($dto->toArray()))->toArray();
            }

            return $rows;
        });

        return [
            'count' => count($updated),
            'items' => $updated,
        ];
    }

    private function assertType(string $type): void
    {
        if (! in_array($type, self::VALID_TYPES, true)) {
            throw new DomainException('Invalid product type', 422, [
                'product_type' => self::VALID_TYPES,
            ]);
        }
    }

    private function assertSkuAvailable(string $sku, ?int $ignoreId = null): void
    {
        if ($this->repository->skuExists($sku, $ignoreId)) {
            throw new DomainException('SKU already exists for current tenant', 422, [
                'sku' => [$sku],
            ]);
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function resolveUnitPayload(array $payload): array
    {
        $unitId = isset($payload['unit_id']) ? (int) $payload['unit_id'] : 0;

        if ($unitId <= 0) {
            throw new DomainException('unit_id is required', 422, [
                'unit_id' => ['required'],
            ]);
        }

        $unit = Unit::query()
            ->where('is_active', true)
            ->find($unitId);

        if ($unit === null) {
            throw new DomainException('Unit is invalid or inactive', 422, [
                'unit_id' => [$unitId],
            ]);
        }

        $payload['unit_id'] = $unit->id;
        return $payload;
    }
}
