<?php
namespace falkirks\simpleportals;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Server;

class PortalStore implements Listener{
    /** @var  Portal[] */
    private $portals;
    public function __construct(array $portals = [], SimplePortals $plugin){
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
        $this->portals = $portals;
    }
    public function removePortal(Portal $portal){
        foreach($this->portals as $i => $testPortal){
            if($testPortal === $portal){
                unset($this->portals[$i]);
                return;
            }
        }
        $this->save();
    }
    public function addPortal(Portal $portal, $name = null){
        if($name == null) $name = $portal->getName();
        else $portal->setName($name);
        $this->portals[] = $portal;
        $this->save();
    }
    public function addPortals(array $portals){
        foreach($portals as $portal){
            $this->addPortal($portal);
        }
    }
    public function onBlockBreak(BlockBreakEvent $event){
        $portal = $this->getPortalAtPoint($event->getBlock());
        if($portal instanceof Portal){
            $event->getPlayer()->sendMessage("That block is inside a portal, it is protected.");
            if($event->getPlayer()->hasPermission("simpleportals.command")){
                $event->getPlayer()->sendMessage("You can delete this portal in the config.");
            }
            $event->setCancelled(true);
        }
    }
    public function onBlockPlace(BlockPlaceEvent $event){
        $portal = $this->getPortalAtPoint($event->getBlock());
        if($portal instanceof Portal){
            $event->getPlayer()->sendMessage("That block is inside a portal, it is protected.");
            if($event->getPlayer()->hasPermission("simpleportals.command")){
                $event->getPlayer()->sendMessage("You can delete this portal in the config.");
            }
            $event->setCancelled(true);
        }
    }
    public function onPlayerMove(PlayerMoveEvent $event){
        //$event->getPlayer()->sendMessage("hey");

        $portal = $this->getPortalAtPoint(Position::fromObject($event->getPlayer()->getPosition()->round(), $event->getPlayer()->getLevel()));
        if($portal instanceof Portal){
            $portal->playerInside($event->getPlayer());
        }
    }
    public function getPortalAtPoint(Position $point){
        foreach($this->portals as $portal){
            if($point->getLevel()->getName() === $portal->getLevel()->getName()) {
                if ($portal->isInside($point)) {
                    return $portal;
                }
            }
        }
        return null;
    }
    public function save(){
        file_put_contents(Server::getInstance()->getPluginManager()->getPlugin("SimplePortals")->getDataFolder() . "portals.json", json_encode($this->toArray(), JSON_PRETTY_PRINT));
    }
    public function toArray(){
        $arr = [];
        foreach($this->portals as $portal){
            $arr[] = $portal->toArray();
        }
        return $arr;
    }
    public static function fromJSON(){
        $plugin = Server::getInstance()->getPluginManager()->getPlugin("SimplePortals");
        if(!file_exists($plugin->getDataFolder() . "portals.json")) return new PortalStore([], $plugin);
        $obj = json_decode(file_get_contents($plugin->getDataFolder() . "portals.json"), true);
        $arr = [];
        foreach($obj as $portalArr){
            $arr[] = Portal::fromArray($portalArr);
        }
        $store = new PortalStore($arr, $plugin);
        $plugin->getServer()->getPluginManager()->registerEvents($store, $plugin);
        return $store;
    }
    /**
     * @return Portal[]
     */
    public function getPortals(){
        return $this->portals;
    }

}