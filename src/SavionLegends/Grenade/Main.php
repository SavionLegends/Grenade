<?php

namespace SavionLegends\Grenade;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
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
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, []);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getLogger()->info("Enabled!");
    }

    public function onDisable(){

    }

    /**
     * @param Player $player
     * @param Position $position
     */
    public function spawnTNT(Player $player, Position $position){
        $tnt = Entity::createEntity("PrimedTNT", $player->getLevel(), Entity::createBaseNBT($position), $player);
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
        $explosion->explodeA();

        foreach($nearbyEntities as $entity){
            if(!$entity instanceof Player){
                break;
            }
            $event = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_CUSTOM, 15);
            $entity->attack($event);
        }
    }
}
