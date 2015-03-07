<?php
namespace falkirks\simpleportals;


use falkirks\simpleportals\exception\PortalAlreadyExistsException;
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
    public function getPortal($name){
        if(!isset($this->portals[$name])) return null;
        return $this->portals[$name];
    }
    public function addPortal(Portal $portal, $name = null){
        if($name == null) $name = $portal->getName();
        else $portal->setName($name);
        if (isset($this->portals[$name])) throw new PortalAlreadyExistsException;
        $this->portals[$name] = $portal;
        var_dump($this->portals);
    }
    public function addPortals(array $portals){
        foreach($portals as $portal){
            $this->addPortal($portal);
        }
    }
    public function onBlockBreak(BlockBreakEvent $event){
        $event->getPlayer()->sendMessage("Block break");
        $portal = $this->getPortalAtPoint($event->getBlock());
        if($portal instanceof Portal){
            $event->getPlayer()->sendMessage("That block is inside a portal, it is protected.");
            if($event->getPlayer()->hasPermission("simpleportals.command")){
                $event->getPlayer()->sendMessage("Use \"/portals delete ". $portal->getName() . "\" to remove this portal.");
            }
            $event->setCancelled(true);
        }
    }
    public function onBlockPlace(BlockPlaceEvent $event){
        $event->getPlayer()->sendMessage("Block place");
        $portal = $this->getPortalAtPoint($event->getBlock());
        if($portal instanceof Portal){
            $event->getPlayer()->sendMessage("That block is inside a portal, it is protected.");
            if($event->getPlayer()->hasPermission("simpleportals.command")){
                $event->getPlayer()->sendMessage("Use \"/portals delete ". $portal->getName() . "\" to remove this portal.");
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
        file_put_contents(Server::getInstance()->getPluginManager()->getPlugin("SimplePortals")->getDataFolder() . "portals.dat", serialize($this));
    }
    public static function fromBinary(){
        $plugin = Server::getInstance()->getPluginManager()->getPlugin("SimplePortals");
        if(!file_exists($plugin->getDataFolder() . "portals.dat")) return new PortalStore([], $plugin);
        $obj = unserialize(file_get_contents($plugin->getDataFolder() . "portals.dat"));
        $plugin->getServer()->getPluginManager()->registerEvents($obj, $plugin);
        return $obj;
    }
    /**
     * @return Portal[]
     */
    public function getPortals(){
        return $this->portals;
    }

}