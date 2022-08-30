<?php

declare(strict_types=1);

namespace Nerahikada\Beeeee;

use Nerahikada\Beeeee\assistant\PeAssistant;
use Nerahikada\Beeeee\assistant\PcAssistant;
use pocketmine\Player;

class Assistant{

	public static function Pe(Player $player) : PeAssistant{
		return new PeAssistant($player, true);
	}

	public static function Pc(Player $player) : PcAssistant{
		return new PcAssistant($player, true);
	}
}