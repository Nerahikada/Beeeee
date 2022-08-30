<?php

declare(strict_types=1);

namespace Nerahikada\Beeeee\assistant;

use pocketmine\entity\Entity;

class AimResult{

	private Entity $entity;
	private float $distance;

	public function __construct(Entity $entity, float $distanceSquared){
		$this->entity = $entity;
		$this->distance = sqrt($distanceSquared);
	}

	public function getEntity() : Entity{
		return $this->entity;
	}

	public function getDistance() : float{
		return $this->distance;
	}
}