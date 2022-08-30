<?php

declare(strict_types=1);

namespace Nerahikada\Beeeee\converter;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Zombie;

/**
 * This class fills in the gap in visual appearance with the size of the entity defined in PMMP.
 */
class EntityVisuallySize{

	private static array $list = [];

	public static function init() : void{
		self::register(Human::class, new EntitySize(0, 1.9, 0));
		self::register(Zombie::class, new EntitySize(0, 2.0, 0));
	}

	public static function get(Entity $entity) : ?EntitySize{
		return self::$list[get_class($entity)] ?? null;
	}

	public static function getA(Entity $entity) : array{
		return self::get($entity) ?? [0, 0, 0];
	}

	public static function register(string $class, EntitySize $size, bool $override = false) : void{
		if(!$override && isset(self::$list[$class])){
			throw new \RuntimeException("Trying to overwrite an already registered entity size");
		}
		self::$list[$class] = $size;
	}
}