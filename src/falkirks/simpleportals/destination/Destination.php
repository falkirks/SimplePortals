<?php

namespace falkirks\simpleportals\destination;


use pocketmine\Player;

interface Destination {
    public function receivePlayer(Player $player);
}