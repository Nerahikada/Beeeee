<?php

declare(strict_types=1);

namespace Nerahikada\Beeeee;

use Nerahikada\Beeeee\converter\EntityVisuallySize;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{

	private static self $instance;

	public static function getInstance() : self{
		return self::$instance;
	}

	public function onEnable() : void{
		self::$instance = $this;
		EntityVisuallySize::init();
	}
}