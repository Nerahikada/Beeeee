<?php

declare(strict_types=1);

namespace Nerahikada\Beeeee\assistant;

use Nerahikada\Beeeee\Main;
use Nerahikada\Beeeee\task\AssistantTickingTask;
use pocketmine\Player;
use pocketmine\scheduler\TaskHandler;

abstract class Assistant{

	protected ?TaskHandler $taskHandler = null;
	protected Player $player;
	protected bool $enabled = false;

	public function __construct(Player $player, bool $enabled = false){
		$this->player = $player;
		$enabled ? $this->enable() : $this->disable();
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function isEnabled() : bool{
		return $this->enabled;
	}

	public function enable() : void{
		$this->enabled = true;
		if($this->taskHandler === null){
			$this->taskHandler = Main::getInstance()->getScheduler()->scheduleRepeatingTask(new AssistantTickingTask($this), 1);
		}
	}

	public function disable() : void{
		$this->enabled = false;
		if($this->taskHandler !== null){
			$this->taskHandler->cancel();
			$this->taskHandler = null;
		}
	}

	abstract public function doTick() : void;

	final protected function canContinue() : bool{
		if($this->player->isOnline()){
			return true;
		}
		$this->disable();
		return false;
	}
}