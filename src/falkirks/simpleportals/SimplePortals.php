<?php
namespace falkirks\simpleportals;

use pocketmine\plugin\PluginBase;

class SimplePortals extends PluginBase{
    /** @var  CreationListener */
    private $creationListener;
    /** @var  PortalStore */
    private $portalStore;
    public function onEnable(){
        $this->saveDefaultConfig();
        $this->portalStore = PortalStore::fromJSON();
        $this->creationListener = new CreationListener($this);
    }
    public function onDisable(){
        $this->portalStore->save();
    }
    /**
     * @return CreationListener
     */
    public function getCreationListener(){
        return $this->creationListener;
    }

    /**
     * @return PortalStore
     */
    public function getPortalStore(){
        return $this->portalStore;
    }

    /**
     * @return \falkirks\simplewarp\SimpleWarp
     */
    public function getSimpleWarp(){
        return $this->getServer()->getPluginManager()->getPlugin("SimpleWarp");
    }

}