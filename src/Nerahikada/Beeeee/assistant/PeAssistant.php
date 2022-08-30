<?php

declare(strict_types=1);

namespace Nerahikada\Beeeee\assistant;

class PeAssistant extends CombatAssistant{

	protected function getReach() : float{
		return 7.0;
	}

	public function doTick() : void{
		if(!$this->canContinue()) return;

		$start = $this->player->add(0, $this->player->getEyeHeight(), 0);

		$targets = $this->getNearbyEntities($start, $this->getReach() + 2.0);
		if(empty($targets)) return;

		foreach($targets as $target){
			$yaw = rad2deg(atan2($target->x - $this->player->x, $target->z - $this->player->z));
			$yaw = $yaw < 0 ? -$yaw : 360 - $yaw;
			$distance = sqrt((($this->player->x - $target->x) ** 2) + (($this->player->z - $target->z) ** 2));
			$pitch = -rad2deg(atan2(($target->y/*+ 1.0*/) - ($this->player->y + $this->player->getEyeHeight()), $distance));

			// https://stackoverflow.com/questions/12234574/calculating-if-an-angle-is-between-two-angles
			$diff = (abs($this->player->yaw - $yaw) + 180) % 360 - 180;
			if(!($diff <= $this->getFovX() && $diff >= -$this->getFovX())) continue;
			$diff = (abs($this->player->pitch - $pitch) + 180) % 360 - 180;
			if(!($diff <= $this->getFovY() && $diff >= -$this->getFovY())) continue;

			$end = $target->add(0, 1.0, 0);

			$result = $this->canAim($start, $end);
			if($result === null || $result->getDistance() > $this->getReach()) continue;

			$this->swingArm();
			$this->attackTo($result->getEntity());
			break;
		}
	}

	private function getFov() : float{
		return 60;
	}

	private function getFovX() : float{
		return ($this->getFov() + 30) / 2;
	}

	private function getFovY() : float{
		return $this->getFov() * (9 / 16);
	}
}