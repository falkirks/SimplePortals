<?php
namespace falkirks\simpleportals\destination;

use pocketmine\Player;

class PortalNotSetDestination implements Destination{
    public function receivePlayer(Player $player){
        $player->sendMessage("This portal hasn't been configured to point to a destination.");
    }

}