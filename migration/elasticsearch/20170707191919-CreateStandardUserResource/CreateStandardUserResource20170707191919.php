<?php

namespace Dlx\Security\Migration\Elasticsearch;

use Daikon\Dbal\Migration\MigrationInterface;
use Daikon\Elasticsearch5\Migration\Elasticsearch5MigrationTrait;

final class CreateStandardUserResource20170707191919 implements MigrationInterface
{
    use Elasticsearch5MigrationTrait;

    public function getDescription(string $direction = self::MIGRATE_UP): string
    {
        return $direction === self::MIGRATE_UP
            ? 'Create User resource standard projection Elasticsearch mapping.'
            : 'Delete User resource standard projection Elasticsearch mapping.';
    }

    public function isReversible(): bool
    {
        return true;
    }

    private function up(): void
    {
        $this->putMappings(
            $this->getIndexName(),
            ['dlx-security-user-standard' => $this->loadFile('user-standard-mapping-20170707191919.json')]
        );
    }

    private function down(): void
    {
        $alias = $this->getIndexName();
        $currentIndex = current($this->getIndicesWithAlias($alias));
        $revertedIndex = $currentIndex.'.reverted';
        $this->reindexWithMappings($currentIndex, $revertedIndex, ['dlx-security-user-standard' => null]);
        $this->reassignAlias($revertedIndex, $alias);
        $this->deleteIndex($currentIndex);
    }

    private function loadFile(string $filename): array
    {
        return json_decode(file_get_contents(__DIR__.'/'.$filename), true);
    }
}
