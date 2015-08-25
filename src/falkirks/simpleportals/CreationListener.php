<?php
namespace falkirks\simpleportals;

use falkirks\simpleportals\destination\PortalNotSetDestination;
use falkirks\simplewarp\Warp;
use pocketmine\block\Block;
use pocketmine\block\Diamond;
use pocketmine\block\DiamondOre;
use pocketmine\block\Obsidian;
use pocketmine\block\StainedClay;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\FlintSteel;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\UpdateBlockPacket;

class CreationListener implements Listener{
    /** @var  SimplePortals */
    private $plugin;
    /** @var Portal[] */
    private $sessions;
    public function __construct(SimplePortals $plugin){
        $this->plugin = $plugin;
        $this->sessions = [];
        $plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
    }
    public function onPlayerInteract(PlayerInteractEvent $event){
        if($event->getPlayer()->hasPermission("simpleportals.create")) {
            if ($event->getItem() instanceof FlintSteel) {
                if ($event->getBlock() instanceof Obsidian || $event->getBlock() instanceof StainedClay) {
                    list($min, $max) = $this->getBounds($event->getBlock());
                    if ($min !== $max) {
                        if (!isset($this->sessions[$event->getPlayer()->getName()])) {
                            $portal = new Portal($this->plugin, $min, $max, $event->getBlock()->getLevel(), "--IN-PROGRESS--" . $event->getPlayer()->getName());
                            //$event->getPlayer()->getLevel()->setBlock($portal->getPos1(), new Diamond());
                            //$event->getPlayer()->getLevel()->setBlock($portal->getPos2(), new Diamond());
                            $event->getPlayer()->sendMessage("Portal generated. Enter a warp name to link the portal to:");
                            $this->sessions[$event->getPlayer()->getName()] = $portal;
                            $event->setCancelled();
                        }
                    }
                }
            }
        }
    }
    public function onPlayerChat(PlayerChatEvent $event){
        if(isset($this->sessions[$event->getPlayer()->getName()])){
            $event->setCancelled();
            $portal = $this->sessions[$event->getPlayer()->getName()];
            /** @var Warp $warp */
            $warp = $this->plugin->getSimpleWarp()->getWarpManager()[$event->getMessage()];
            if($warp instanceof Warp) {
                $portal->setName($event->getMessage());
                $this->plugin->getPortalStore()->addPortal($portal);
                $event->getPlayer()->sendMessage("Portal created.");
                unset($this->sessions[$event->getPlayer()->getName()]);
            }
            elseif($event->getMessage() === "exit"){
                $event->getPlayer()->sendMessage("Portal creation cancelled.");
                unset($this->sessions[$event->getPlayer()->getName()]);
            }
            else{
                $event->getPlayer()->sendMessage("There is no warp with that name, try again.");
            }
        }
    }
    private function getBounds(Block $block){
        $min = new Vector3($block->getX(), $block->getY(), $block->getZ());
        $max = new Vector3($block->getX(), $block->getY(), $block->getZ());

        $id = $block->getId();
        $meta = $block->getDamage();
        $queue = [$block];
        $processed = [];
        while(!empty($queue)){
            /** @var Block $block */
            $block = array_pop($queue);
            if($block->getId() == $id && $block->getDamage() == $meta){
                if($block->getX() > $max->getX()) $max->x = $block->getX();
                if($block->getY() > $max->getY()) $max->y = $block->getY();
                if($block->getZ() > $max->getZ()) $max->z = $block->getZ();

                if($block->getX() < $min->getX()) $min->x = $block->getX();
                if($block->getY() < $min->getY()) $min->y = $block->getY();
                if($block->getZ() < $min->getZ()) $min->z = $block->getZ();

                $next = $block->getLevel()->getBlock($block->add(1));
                if(!in_array($next, $processed)) $queue[] = $next;

                $next = $block->getLevel()->getBlock($block->add(-1));
                if(!in_array($next, $processed)) $queue[] = $next;

                $next = $block->getLevel()->getBlock($block->add(0, 1));
                if(!in_array($next, $processed)) $queue[] = $next;

                $next = $block->getLevel()->getBlock($block->add(0, -1));
                if(!in_array($next, $processed)) $queue[] = $next;

                $next = $block->getLevel()->getBlock($block->add(0, 0, 1));
                if(!in_array($next, $processed)) $queue[] = $next;

                $next = $block->getLevel()->getBlock($block->add(0, 0, -1));
                if(!in_array($next, $processed)) $queue[] = $next;
                $processed[] = $block;
            }
        }
        return [$min, $max];
    }
}