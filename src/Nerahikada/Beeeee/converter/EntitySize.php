<?php

declare(strict_types=1);

namespace Nerahikada\Beeeee\converter;

class EntitySize{

	private float $x;
	private float $y;
	private float $z;

	public function __construct(float $x = 0.0, float $y = 0.0, float $z = 0.0){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
	}

	public function toArray() : array{
		return [$this->x, $this->y, $this->z];
	}

	public function getX() : float{
		return $this->x;
	}

	public function getY() : float{
		return $this->y;
	}

	public function getZ() : float{
		return $this->z;
	}
}