<?php

namespace Dlx\Security\User\Repository\Standard;

use Daikon\Dbal\Storage\StorageAdapterInterface;
use Daikon\ReadModel\Projection\ProjectionInterface;
use Daikon\ReadModel\Projection\ProjectionMap;
use Daikon\ReadModel\Repository\RepositoryInterface;

final class UserRepository implements RepositoryInterface
{
    private $storageAdapter;

    public function __construct(StorageAdapterInterface $storageAdapter)
    {
        $this->storageAdapter = $storageAdapter;
    }

    public function findById(string $identifier): ProjectionInterface
    {
        return $this->storageAdapter->read($identifier);
    }

    public function findByIds(array $identifiers): ProjectionMap
    {
    }

    public function search($query, $from, $size): ProjectionMap
    {
        return $this->storageAdapter->search($query, $from, $size);
    }

    public function persist(ProjectionInterface $projection): bool
    {
        return $this->storageAdapter->write($projection->getAggregateId(), $projection->toArray());
    }

    public function makeProjection(): ProjectionInterface
    {
        return User::fromArray([
            '@type' => User::class,
            '@parent' => null
        ]);
    }
}
