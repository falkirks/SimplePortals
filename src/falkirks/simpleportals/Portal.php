<?php
namespace falkirks\simpleportals;

use falkirks\simplewarp\Warp;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;

class Portal{
    /** @var SimplePortals  */
    private $plugin;
    /** @var Vector3  */
    private $pos1;
    /** @var Vector3  */
    private $pos2;
    /** @var Level  */
    private $level;
    /** @var Position  */
    private $entryPoint;
    /** @var string */
    private $name;
    /** @var  Player[] */
    protected $players;
    public function __construct(SimplePortals $plugin, Vector3 $pos1, Vector3 $pos2, Level $level, $name){
        $this->plugin = $plugin;
        $this->level = $level;
        $this->pos1 = new Vector3(min($pos1->x, $pos2->x), min($pos1->y, $pos2->y), min($pos1->z, $pos2->z));
        $this->pos2 = new Vector3(max($pos1->x, $pos2->x), max($pos1->y, $pos2->y), max($pos1->z, $pos2->z));
        $this->entryPoint = new Position(($pos1->x + $pos2->x)/2, $this->pos1->y, ($pos1->z + $pos2->z)/2, $level);
        $this->name = $name;
        $this->players = [];
        $this->fillPortal(...explode(":", $plugin->getConfig()->get('portal-fill-block')));

    }
    protected function fillPortal($id, $meta = 0){
        for($x = $this->pos1->x; $x <= $this->pos2->x; $x++){
            for($y = $this->pos1->y; $y <= $this->pos2->y; $y++){
                for($z = $this->pos1->z; $z <= $this->pos2->z; $z++){
                    if($this->level->getBlockIdAt($x, $y, $z) === 0) {
                        $this->level->setBlockIdAt($x, $y, $z, $id);
                        $this->level->setBlockDataAt($x, $y, $z, $meta);
                    }
                }
            }
        }
    }
    /**
     * @return Level
     */
    public function getLevel(){
        return $this->level;
    }
    public function playerInside(Player $player){
        if($player->hasPermission("simpleportals.use")) {
            if (!isset($this->players[$player->getName()]) || $this->players[$player->getName()] + $this->getPlugin()->getConfig()->get("portal-entry-throttle") < time()) {
                $this->players[$player->getName()] = time();
                /** @var Warp $warp */
                $warp = $this->getPlugin()->getSimpleWarp()->getWarpManager()[$this->name];
                if ($warp instanceof Warp) {
                    if ($warp->canUse($player)) {
                        $warp->teleport($player);
                        $player->sendMessage("Teleporting...");
                    }
                    else {
                        $player->sendMessage("You don't have permission to use this warp.");
                    }
                }
                else {
                    // Warp doesn't exist, delete the portal.
                    $this->getPlugin()->getPortalStore()->removePortal($this);
                }
            }
        }
    }
    public function getEntryPoint(){
        return $this->entryPoint;
    }
    public function isInside(Vector3 $point){
        return ($point->x >= $this->pos1->x && $point->x <= $this->pos2->x) && ($point->y >= $this->pos1->y && $point->y <= $this->pos2->y) && ($point->z >= $this->pos1->z && $point->z <= $this->pos2->z);
    }
    /**
     * @return string
     */
    public function getName(){
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name){
        $this->name = $name;
    }

    /**
     * @return Vector3
     */
    public function getPos1(){
        return $this->pos1;
    }

    /**
     * @return Vector3
     */
    public function getPos2(){
        return $this->pos2;
    }

    /**
     * @return SimplePortals
     */
    public function getPlugin(){
        return $this->plugin;
    }

    public function toArray(){
        return [
            "pos1" => [
                "x" => $this->pos1->x,
                "y" => $this->pos1->y,
                "z" => $this->pos1->z
            ],
            "pos2" => [
                "x" => $this->pos2->x,
                "y" => $this->pos2->y,
                "z" => $this->pos2->z
            ],
            "level" => $this->getLevel()->getName(),
            "name" => $this->getName()
        ];
    }
    public static function fromArray($array){
        return new Portal(Server::getInstance()->getPluginManager()->getPlugin("SimplePortals"), new Vector3($array["pos1"]["x"], $array["pos1"]["y"], $array["pos1"]["z"]), new Vector3($array["pos2"]["x"], $array["pos2"]["y"], $array["pos2"]["z"]), Server::getInstance()->getLevelByName($array["level"]), $array["name"]);
    }

}