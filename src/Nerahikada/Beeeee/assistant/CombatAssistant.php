<?php

declare(strict_types=1);

namespace Nerahikada\Beeeee\assistant;

use Nerahikada\Beeeee\converter\EntityVisuallySize;
use pocketmine\entity\Creature;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\Player;

abstract class CombatAssistant extends Assistant{

	private AxisAlignedBB $temporaryBoundingBox;

	public function __construct(Player $player, bool $enabled = false){
		parent::__construct($player, $enabled);

		$this->temporaryBoundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
	}

	abstract protected function getReach() : float;

	protected function canAim(Vector3 $start, Vector3 $end) : ?AimResult{
		$this->temporaryBoundingBox->setBounds($start->x - 0.01, $start->y, $start->z - 0.01, $start->x + 0.01, $start->y + 0.02, $start->z + 0.01);

		$entityHit = null;

		foreach(VoxelRayTrace::betweenPoints($start, $end) as $vector3){
			$block = $this->player->level->getBlockAt($vector3->x, $vector3->y, $vector3->z);
			$blockHitResult = $block->calculateIntercept($start, $end);
			if($blockHitResult !== null){
				$end = $blockHitResult->hitVector;
				break;
			}
		}

		$entityDistance = PHP_INT_MAX;

		$newDiff = $end->subtract($start);
		foreach($this->player->level->getCollidingEntities($this->temporaryBoundingBox->addCoord($newDiff->x, $newDiff->y, $newDiff->z)->expand(1, 1, 1)) as $entity){
			if($entity->getId() === $this->player->getId()){
				continue;
			}

			$entityBB = $entity->boundingBox;
			if(($size = EntityVisuallySize::get($entity)) !== null){
				$entityBB = $entityBB->addCoord(...$size->toArray());
			}

			$entityHitResult = $entityBB->calculateIntercept($start, $end);

			if($entityHitResult === null){
				continue;
			}

			$distance = $start->distanceSquared($entityHitResult->hitVector);
			if($distance < $entityDistance){
				$entityDistance = $distance;
				$entityHit = $entity;
			}
		}

		return $entityHit !== null ? new AimResult($entityHit, $entityDistance) : null;
	}

	public function swingArm() : void{
		$packet = new AnimatePacket();
		$packet->action = AnimatePacket::ACTION_SWING_ARM;
		$packet->entityRuntimeId = $this->player->getId();
		$this->player->getServer()->broadcastPacket($this->player->getViewers(), $packet);
		$this->player->dataPacket($packet);
	}

	public function attackTo(Entity $entity) : void{
		$packet = new InventoryTransactionPacket();
		$packet->trData = UseItemOnEntityTransactionData::new(
			[],
			$entity->getId(),
			UseItemOnEntityTransactionData::ACTION_ATTACK,
			0,
			ItemStackWrapper::legacy(Item::get(Item::AIR)),
			$this->player->asVector3(),
			new Vector3()
		);
		$this->player->handleInventoryTransaction($packet);
	}

	/**
	 * @see Level::getNearestEntity()
	 */
	public function getNearestEntity(Vector3 $pos, float $maxDistance, string $entityType = Creature::class, bool $includeDead = false) : ?Entity{
		assert(is_a($entityType, Entity::class, true));

		$minX = ((int) floor($pos->x - $maxDistance)) >> 4;
		$maxX = ((int) floor($pos->x + $maxDistance)) >> 4;
		$minZ = ((int) floor($pos->z - $maxDistance)) >> 4;
		$maxZ = ((int) floor($pos->z + $maxDistance)) >> 4;

		$currentTargetDistSq = $maxDistance ** 2;
		$currentTarget = null;

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				foreach($this->player->level->getChunkEntities($x, $z) as $entity){
					if(!($entity instanceof $entityType) || $entity->isClosed() || $entity->isFlaggedForDespawn() || (!$includeDead && !$entity->isAlive()) || $entity->getId() === $this->player->getId()){
						continue;
					}
					$distSq = $entity->distanceSquared($pos);
					if($distSq < $currentTargetDistSq){
						$currentTargetDistSq = $distSq;
						$currentTarget = $entity;
					}
				}
			}
		}

		return $currentTarget;
	}

	/**
	 * Returns the entities sorted by nearest
	 * @see Level::getNearestEntity()
	 */
	public function getNearbyEntities(Vector3 $pos, float $maxDistance, string $entityType = Creature::class, bool $includeDead = false) : array{
		assert(is_a($entityType, Entity::class, true));

		$minX = ((int) floor($pos->x - $maxDistance)) >> 4;
		$maxX = ((int) floor($pos->x + $maxDistance)) >> 4;
		$minZ = ((int) floor($pos->z - $maxDistance)) >> 4;
		$maxZ = ((int) floor($pos->z + $maxDistance)) >> 4;

		$targets = [];

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				foreach($this->player->level->getChunkEntities($x, $z) as $entity){
					if(!($entity instanceof $entityType) || $entity->isClosed() || $entity->isFlaggedForDespawn() || (!$includeDead && !$entity->isAlive()) || $entity->getId() === $this->player->getId()){
						continue;
					}
					$distSq = $entity->distanceSquared($pos);
					if($distSq < $maxDistance ** 2){
						$targets[] = [$distSq, $entity];
					}
				}
			}
		}

		if(!empty($targets)){
			array_multisort(
				array_column($targets, 0), SORT_ASC, SORT_REGULAR,
				range(1, count($targets)), SORT_ASC, SORT_NUMERIC,	//これがないと"nesting level too deep"で殺される
				$targets, SORT_ASC, SORT_REGULAR
			);
			return array_column($targets, 1);
		}

		return $targets;
	}
}