<?php

declare(strict_types=1);

namespace Nerahikada\Beeeee\task;

use Nerahikada\Beeeee\assistant\Assistant;
use pocketmine\scheduler\Task;

class AssistantTickingTask extends Task{

	private Assistant $assistant;

	public function __construct(Assistant $assistant){
		$this->assistant = $assistant;
	}

	public function onRun(int $currentTick) : void{
		$this->assistant->doTick();
	}
}