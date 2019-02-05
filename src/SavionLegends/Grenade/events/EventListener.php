<?php


namespace SavionLegends\Grenade\events;


use pocketmine\entity\Entity;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\entity\projectile\Egg;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use SavionLegends\Grenade\Main;

class EventListener implements Listener {

    private $plugin, $server;

    /**
     * EventListener constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        $this->server = $plugin->getServer();
    }

    /**
     * @return Main
     */
    public function getPlugin(): Main{
        return $this->plugin;
    }

    /**
     * @return \pocketmine\Server
     */
    public function getServer(): \pocketmine\Server{
        return $this->server;
    }

    /**
     * @param ExplosionPrimeEvent $event
     */
    public function onExplode(ExplosionPrimeEvent $event){
        $player = $event->getEntity()->getOwningEntity();
        $entity = $event->getEntity();
        if($player instanceof Player && isset(Main::$usingGrenade[$player->getName()])){
            $this->getPlugin()->explode($entity->getPosition(), $player);
            $event->setCancelled(true);
        }
    }

    /**
     * @param ProjectileHitEntityEvent $event
     */
    public function onProjectileHit(ProjectileHitEntityEvent $event){
        $projectile = $event->getEntity();
        if($projectile instanceof Egg && $event->getEntityHit() instanceof Player or $event->getEntityHit() instanceof Entity && !$event->getEntityHit() instanceof PrimedTNT){
            $shooter = $projectile->getOwningEntity();
            $pos = $event->getEntityHit()->getPosition();
            $dropItem = $projectile->getLevel()->dropItem(new Vector3($pos->x, $pos->y + 1.5, $pos->z), Item::get(Item::EGG));
            Main::$dropItems[$dropItem->getId()] = $dropItem;
            if($shooter instanceof Player && isset(Main::$usingGrenade[$shooter->getName()])){
                $this->getPlugin()->spawnTNT($shooter, $dropItem);
            }
        }
    }

    /**
     * @param ProjectileHitBlockEvent $event
     */
    public function onProjectileHitBlock(ProjectileHitBlockEvent $event){
        $projectile = $event->getEntity();
        if($projectile instanceof Egg){
            $shooter = $projectile->getOwningEntity();
            $pos = $event->getBlockHit();
            $dropItem = $projectile->getLevel()->dropItem(new Vector3($pos->x, $pos->y + 1.5, $pos->z), Item::get(Item::EGG));
            Main::$dropItems[$dropItem->getId()] = $dropItem;
            if($shooter instanceof Player && isset(Main::$usingGrenade[$shooter->getName()])){
                $this->getPlugin()->spawnTNT($shooter, $dropItem);
            }
        }
    }
}
