<?php
namespace falkirks\simpleportals;

use pocketmine\plugin\PluginBase;

class SimplePortals extends PluginBase{
    /** @var  CreationListener */
    private $creationListener;
    /** @var  PortalStore */
    private $portalStore;
    public function onEnable(){
        $this->getLogger()->warning("SimplePortals is in development, mind the bugs, they can bite!");
        $this->portalStore = PortalStore::fromBinary();
        var_dump($this->portalStore);
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

}