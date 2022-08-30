<?php

declare(strict_types=1);

namespace Nerahikada\Beeeee\assistant;

class PcAssistant extends CombatAssistant{

	protected function getReach() : float{
		return 4.35;
	}

	public function doTick() : void{
		if(!$this->canContinue()) return;

		$start = $this->player->add(0, $this->player->getEyeHeight(), 0);
		$target = $this->getNearestEntity($start, $this->getReach() + 2.0);
		if($target === null) return;
		$end = $start->add($this->player->getDirectionVector()->multiply($this->getReach() + 1.0));

		$result = $this->canAim($start, $end);
		if($result === null || $result->getDistance() > $this->getReach()) return;

		$this->swingArm();
		$this->attackTo($result->getEntity());
	}
}