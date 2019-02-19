<?php


namespace SavionLegends\Grenade\events;


use pocketmine\entity\Entity;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\entity\projectile\Egg;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\Listener;
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
            if(Main::$usingGrenade[$player->getName()]["Type"] === Main::FRAG){
                $this->getPlugin()->explodeFrag($entity->getPosition(), $player);
                $event->setCancelled(true);
            }
            if(Main::$usingGrenade[$player->getName()]["Type"] === Main::STUN){
                $this->getPlugin()->explodeStun($entity->getPosition(), $player);
                $event->setCancelled(true);
            }
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
            if($shooter instanceof Player && isset(Main::$usingGrenade[$shooter->getName()])){
                $this->getPlugin()->spawnTNT($shooter, $pos);
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
            if($shooter instanceof Player && isset(Main::$usingGrenade[$shooter->getName()])){
                $this->getPlugin()->spawnTNT($shooter, $pos);
            }
        }
    }
}
