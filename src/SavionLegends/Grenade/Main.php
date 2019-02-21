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
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use SavionLegends\Grenade\commands\CommandClass;
use SavionLegends\Grenade\events\EventListener;

class Main extends PluginBase{

    public const FRAG = "FRAG";
    public const STUN = "STUN";

    public static $types = [self::FRAG => self::FRAG, self::STUN => self::STUN];

    /* @var \pocketmine\utils\Config*/
    private $config;

    private $blockBreak, $damage, $range, $explosionSize;


    public function onLoad(){
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
    }

    public function onEnable(){
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, []);

        $this->blockBreak["Frag"] = $this->config->get("Frag")["Block-break"];
        $this->damage["Frag"] = $this->config->get("Frag")["Damage"];
        $this->range["Frag"] = $this->config->get("Frag")["Range"];
        $this->explosionSize["Frag"] = $this->config->get("Frag")["Explosion-size"];

        $this->blockBreak["Stun"] = $this->config->get("Stun")["Block-break"];
        $this->damage["Stun"] = $this->config->get("Stun")["Damage"];
        $this->range["Stun"] = $this->config->get("Stun")["Range"];
        $this->explosionSize["Stun"] = $this->config->get("Stun")["Explosion-size"];


        CommandClass::registerAll($this, $this->getServer()->getCommandMap());

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getLogger()->info("Enabled!");
    }

    /**
     * @param Player $player
     * @param Position $position
     * @param $type
     */
    public function spawnTNT(Player $player, Position $position, $type){
        $tnt = Entity::createEntity("PrimedTNT", $player->getLevel(), Entity::createBaseNBT($position), $player);
        $tnt->setOwningEntity($player);
        if($tnt->namedtag !== null){
            $tnt->namedtag->setString("Type", $type);
            $tnt->namedtag->setString("Custom", "true");
        }else{
            $tnt->namedtag = new CompoundTag("", []);
            $tnt->namedtag->setString("Type", $type);
            $tnt->namedtag->setString("Custom", "true");
        }
        $tnt->spawnToAll();
    }


    /**
     * @param Position $position
     * @param Player $player
     */
    public function explodeStun(Position $position, Player $player){
        $boundingBox = new AxisAlignedBB($position->getX() - $this->range["Stun"], $position->getY() - $this->range["Stun"], $position->getZ() - $this->range["Stun"], $position->getX() + $this->range["Stun"], $position->getY() + $this->range["Stun"], $position->getZ() + $this->range["Stun"]);

        $nearbyEntities = $player->getLevel()->getNearbyEntities($boundingBox);


        $explosion = new Explosion($position, $this->explosionSize["Stun"], null);

        if($this->blockBreak["Stun"]){
            $explosion->explodeB();
        }else{
            $explosion->explodeA();
        }

        foreach($nearbyEntities as $entity){
            if(!($entity instanceof Player)){
                continue;
            }
            $event = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $this->damage["Stun"]);
            $entity->attack($event);

            $entity->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 20*5, 2));
            $entity->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 20*5, 3));

            $entity->getLevel()->broadcastLevelSoundEvent(new Vector3($position->x, $position->y, $position->z), LevelSoundEventPacket::SOUND_EXPLODE, -1, -1, false, true);
        }
    }

    /**
     * @param Position $position
     * @param Player $player
     */
    public function explodeFrag(Position $position, Player $player){
        $boundingBox = new AxisAlignedBB($position->getX() - $this->range["Frag"], $position->getY() - $this->range["Frag"], $position->getZ() - $this->range["Frag"], $position->getX() + $this->range["Frag"], $position->getY() + $this->range["Frag"], $position->getZ() + $this->range["Frag"]);

        $nearbyEntities = $player->getLevel()->getNearbyEntities($boundingBox);


        $explosion = new Explosion($position, $this->explosionSize["Frag"], null);
        if($this->blockBreak["Frag"]){
            $explosion->explodeB();
        }else{
            $explosion->explodeA();
        }

        foreach($nearbyEntities as $entity){
            if(!($entity instanceof Player)){
                continue;
            }
            $event = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $this->damage["Frag"]);
            $entity->attack($event);

            $entity->getLevel()->broadcastLevelSoundEvent(new Vector3($position->x, $position->y, $position->z), LevelSoundEventPacket::SOUND_EXPLODE, -1, -1, false, true);
        }
    }

    /**
     * @param Player $player
     * @param $count
     * @param $type
     */
    public function giveGrenade(Player $player, $count, $type){
        if($type === Main::FRAG){
            $item = Item::get(Item::EGG, -1, $count);
            $item->setCustomName("Frag Grenade");
            $nbt = $item->getNamedTag() ?? new CompoundTag("", []);
            $nbt->setString("Type", $type);
            $item->setNamedTag($nbt);
            $player->getInventory()->addItem($item);
            $player->sendMessage(TextFormat::YELLOW."You received ".$type." grenade!");
        }
        if($type === Main::STUN){
            $item = Item::get(Item::EGG, -2, $count);
            $item->setCustomName("Stun Grenade");
            $nbt = $item->getNamedTag() ?? new CompoundTag("", []);
            $nbt->setString("Type", $type);
            $item->setNamedTag($nbt);
            $player->getInventory()->addItem($item);
            $player->sendMessage(TextFormat::YELLOW."You received ".$type." grenade!");
        }
    }
}
