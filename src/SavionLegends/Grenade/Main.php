<?php

namespace SavionLegends\Grenade;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use SavionLegends\Grenade\commands\CommandClass;
use SavionLegends\Grenade\events\EventListener;

class Main extends PluginBase{

    /* @var \pocketmine\utils\Config*/
    private $config;

    public static $dropItems = [];
    public static $usingGrenade = [];

    public function onLoad(){
        @mkdir($this->getDataFolder());
    }

    public function onEnable(){
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, ["Block-break" => false]);

        CommandClass::registerAll($this, $this->getServer()->getCommandMap());
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getLogger()->info("Enabled!");
    }

    public function onDisable(){

    }

    /**
     * @param Player $player
     * @param Entity $entity
     */
    public function spawnTNT(Player $player, Entity $entity){
        $tnt = Entity::createEntity("PrimedTNT", $player->getLevel(), Entity::createBaseNBT($entity->getPosition()), $player);
        $tnt->setOwningEntity($player);
        $tnt->spawnToAll();
    }

    /**
     * @param Position $position
     * @param Player $player
     */
    public function explode(Position $position, Player $player){
        $boundingBox = new AxisAlignedBB($position->getX() - 5, $position->getY() - 5, $position->getZ() - 5, $position->getX() + 5, $position->getY() + 5, $position->getZ() + 5);

        $nearbyEntities = $player->getLevel()->getNearbyEntities($boundingBox);


        $explosion = new Explosion($position, 4, null);
        if($this->config->get("Block-break") === true){
            $explosion->explodeB();
        }else{
            $explosion->explodeA();
        }

        foreach($nearbyEntities as $entity){
            if(!$entity instanceof Player){
                break;
            }
            $event = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_CUSTOM, 15);
            $entity->attack($event);
        }
        if(isset(self::$usingGrenade[$player->getName()])){
            $beforeCount = self::$usingGrenade[$player->getName()];
            self::$usingGrenade[$player->getName()] = ($beforeCount - 1);
            if(self::$usingGrenade[$player->getName()] === 0){
                unset(self::$usingGrenade[$player->getName()]);
            }
        }
    }

    /**
     * @param Player $player
     * @param $count
     */
    public function giveGrenade(Player $player, $count){
        $item = Item::get(Item::EGG, -1, $count);
        $item->setCustomName("Grenade");
        $player->getInventory()->addItem($item);

        $player->sendMessage(TextFormat::YELLOW."You received ".$count." grenade(s)!");
        if(!isset(self::$usingGrenade[$player->getName()])){
            self::$usingGrenade[$player->getName()] = $count;
        }else{
            $beforeCount = self::$usingGrenade[$player->getName()];
            self::$usingGrenade[$player->getName()] = ($beforeCount + $count);
        }
    }
}
