<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyTerraformingInterface;

/**
 * @method null|ColonyTerraformingInterface find(integer $id)
 */
interface ColonyTerraformingRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyTerraformingInterface;

    public function save(ColonyTerraformingInterface $terraforming): void;

    public function delete(ColonyTerraformingInterface $terraforming): void;

    /**
     * @return ColonyTerraformingInterface[]
     */
    public function getByColony(array $colonyies): array;

    public function getByColonyAndField(int $colonyId, int $fieldId): ?ColonyTerraformingInterface;

    /**
     * @return ColonyTerraformingInterface[]
     */
    public function getFinishedJobs(): array;
}
