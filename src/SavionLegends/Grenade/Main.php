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
use SavionLegends\Grenade\tasks\InventoryCheckTask;

class Main extends PluginBase{

    /* @var \pocketmine\utils\Config*/
    private $config;

    private $blockBreak, $damage, $range;

    public static $dropItems = [];
    public static $usingGrenade = [];

    public function onLoad(){
        @mkdir($this->getDataFolder());
    }

    public function onEnable(){
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, ["Block-break" => false, "Damage" => 10, "Range" => 5]);

        $this->blockBreak = $this->config->get("Block-break");
        $this->damage = $this->config->get("Damage");
        $this->range = $this->config->get("Range");

        if($this->damage < 0){
            $this->damage = 10;
            $this->getLogger()->error(TextFormat::RED."Grenade damage cannot be less than 0!");
        }

        if($this->range < 0){
            $this->damage = 10;
            $this->getLogger()->error(TextFormat::RED."Grenade range cannot be less than 0!");
        }

        CommandClass::registerAll($this, $this->getServer()->getCommandMap());
        $this->getScheduler()->scheduleRepeatingTask(new InventoryCheckTask($this), 20);
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
        $boundingBox = new AxisAlignedBB($position->getX() - $this->range, $position->getY() - $this->range, $position->getZ() - $this->range, $position->getX() + $this->range, $position->getY() + $this->range, $position->getZ() + $this->range);

        $nearbyEntities = $player->getLevel()->getNearbyEntities($boundingBox);


        $explosion = new Explosion($position, 4, null);
        if($this->blockBreak){
            $explosion->explodeB();
        }else{
            $explosion->explodeA();
        }

        foreach($nearbyEntities as $entity){
            if(!($entity instanceof Player)){
                continue;
            }
            $event = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_CUSTOM, $this->damage);
            $entity->attack($event);
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
