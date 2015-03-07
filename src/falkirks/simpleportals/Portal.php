<?php
namespace falkirks\simpleportals;

use falkirks\simpleportals\destination\Destination;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;

class Portal implements Destination, \Serializable{
    const PORTAL_ENTRY_THROTTLE = 3;
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
    /** @var Destination  */
    private $destination;
    /** @var  Player[] */
    protected $players;
    public function __construct(Vector3 $pos1, Vector3 $pos2, Level $level, Destination $destination){
        $this->level = $level;
        $this->pos1 = new Vector3(min($pos1->x, $pos2->x), min($pos1->y, $pos2->y), min($pos1->z, $pos2->z));
        $this->pos2 = new Vector3(max($pos1->x, $pos2->x), max($pos1->y, $pos2->y), max($pos1->z, $pos2->z));
        $this->entryPoint = new Position(($pos1->x + $pos2->x)/2, $this->pos1->y, ($pos1->z + $pos2->z)/2, $level);
        $this->name = "Unnamed";
        $this->destination = $destination;
        $this->players = [];
    }

    /**
     * @return Level
     */
    public function getLevel(){
        return $this->level;
    }
    public function playerInside(Player $player){
        if(!isset($this->players[$player->getName()]) || $this->players[$player->getName()]+Portal::PORTAL_ENTRY_THROTTLE < time()) {
            $this->players[$player->getName()] = time();
            $player->sendMessage("You have entered " . $this->name);
            $this->destination->receivePlayer($player);
        }
    }
    public function receivePlayer(Player $player){
        $player->teleport($this->entryPoint);
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
     * @return Destination
     */
    public function getDestination(){
        return $this->destination;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize(){
        return json_encode(["pos1" => serialize($this->pos1), "pos2" => serialize($this->pos2), "level" => serialize($this->getLevel()->getName()), "destination" => serialize($this->destination)]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized){
        $json = json_decode($serialized, true);
        $this->__construct(unserialize($json["pos1"]), unserialize($json["pos2"]), Server::getInstance()->getLevelByName(unserialize($json["level"])), unserialize($json["destination"]));
    }

}