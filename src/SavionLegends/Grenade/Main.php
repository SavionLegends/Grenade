<?php

namespace SavionLegends\Grenade;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
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

    public const FRAG = "FRAG";
    public const STUN = "STUN";

    public static $types = [self::FRAG => self::FRAG, self::STUN => self::STUN];
    public static $usingGrenade = [];

    /* @var \pocketmine\utils\Config*/
    private $config;

    private $blockBreak, $damage, $range, $explosionSize;


    public function onLoad(){
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
    }

    public function onEnable(){
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, ["Block-break" => false, "Damage" => 10, "Range" => 5]);

        $this->blockBreak = $this->config->get("Block-break");
        $this->damage = $this->config->get("Damage");
        $this->range = $this->config->get("Range");
        $this->explosionSize = $this->config->get("Explosion-size");

        if($this->damage <= 0){
            $this->damage = 10;
            $this->getLogger()->error(TextFormat::RED."Grenade damage cannot be less than 0!");
        }

        if($this->range <= 0){
            $this->damage = 10;
            $this->getLogger()->error(TextFormat::RED."Grenade range cannot be less than 0!");
        }

        if($this->explosionSize <= 0){
            $this->explosionSize = 4;
            $this->getLogger()->error(TextFormat::RED."Grenade explosion size cannot be less than 0!");
        }

        CommandClass::registerAll($this, $this->getServer()->getCommandMap());

        $this->getScheduler()->scheduleRepeatingTask(new InventoryCheckTask($this), 20);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getLogger()->info(TextFormat::GREEN."Enabled!");
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
    public function explodeStun(Position $position, Player $player){
        $boundingBox = new AxisAlignedBB($position->getX() - $this->range, $position->getY() - $this->range, $position->getZ() - $this->range, $position->getX() + $this->range, $position->getY() + $this->range, $position->getZ() + $this->range);

        $nearbyEntities = $player->getLevel()->getNearbyEntities($boundingBox);


        $explosion = new Explosion($position, $this->explosionSize, null);
        $explosion->explodeA();

        foreach($nearbyEntities as $entity){
            if(!($entity instanceof Player)){
                continue;
            }
            $event = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, 0);
            $entity->attack($event);

            $entity->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 20*5, 2));
            $entity->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 20*5, 3));
        }
    }

    /**
     * @param Position $position
     * @param Player $player
     */
    public function explodeFrag(Position $position, Player $player){
        $boundingBox = new AxisAlignedBB($position->getX() - $this->range, $position->getY() - $this->range, $position->getZ() - $this->range, $position->getX() + $this->range, $position->getY() + $this->range, $position->getZ() + $this->range);

        $nearbyEntities = $player->getLevel()->getNearbyEntities($boundingBox);


        $explosion = new Explosion($position, $this->explosionSize, null);
        if($this->blockBreak){
            $explosion->explodeB();
        }else{
            $explosion->explodeA();
        }

        foreach($nearbyEntities as $entity){
            if(!($entity instanceof Player)){
                continue;
            }
            $event = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $this->damage);
            $entity->attack($event);
        }
    }

    /**
     * @param Player $player
     * @param $count
     * @param $type
     */
    public function giveGrenade(Player $player, $count, $type){
       if($type === self::FRAG){
           $item = Item::get(Item::EGG, -1, $count);
           $item->setCustomName("Frag Grenade");
           $player->getInventory()->addItem($item);
       }elseif($type === self::STUN){
           $item = Item::get(Item::EGG, -2, $count);
           $item->setCustomName("Stun Grenade");
           $player->getInventory()->addItem($item);
       }
        $player->sendMessage(TextFormat::YELLOW."You received ".$count." ".$type." grenade(s)!");
        if(!isset(self::$usingGrenade[$player->getName()])){
            self::$usingGrenade[$player->getName()]["Type"] = $type;
            self::$usingGrenade[$player->getName()]["Count"] = $count;
        }else{
            /* TODO: fix
            $beforeCount = self::$usingGrenade[$player->getName()]["Type"]["Count"];
            $beforeType = self::$usingGrenade[$player->getName()]["Type"];
            self::$usingGrenade[$player->getName()][$beforeType]["Count"] = ($beforeCount + $count);*/
            self::$usingGrenade[$player->getName()]["Type"] = $type;
            self::$usingGrenade[$player->getName()]["Count"] = $count;
        }
    }
}
