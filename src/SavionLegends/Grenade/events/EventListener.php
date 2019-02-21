<?php


namespace SavionLegends\Grenade\events;

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
        $entity = $event->getEntity();
        if($entity->namedtag !== null){
            $type = $entity->namedtag->getString("Type");
            $custom = $entity->namedtag->getString("Custom");
            $owningEntity = $entity->getOwningEntity();
            if($owningEntity instanceof Player){
                if($type === Main::FRAG){
                    $event->setCancelled(true);
                    $this->getPlugin()->explodeFrag($entity->getPosition(), $owningEntity);
                }
                if($type === Main::STUN){
                    $event->setCancelled(true);
                    $this->getPlugin()->explodeStun($entity->getPosition(), $owningEntity);
                }
            }
            if($custom === "true" or $custom === true){
                $event->setCancelled(true);
            }
        }
    }

    /**
     * @param ProjectileHitEntityEvent $event
     */
    public function onProjectileHit(ProjectileHitEntityEvent $event){
        $projectile = $event->getEntity();
        $entityHit = $event->getEntityHit();
        if($projectile instanceof Egg){
            $shooter = $projectile->getOwningEntity();
            if($shooter instanceof Player){
                if($projectile->namedtag !== null){
                    $type = $projectile->namedtag->getString("Type");
                    $this->getPlugin()->spawnTNT($shooter, $entityHit->getPosition(), $type);
                }
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
            if($shooter instanceof Player){
                if($projectile->namedtag !== null){
                    $type = $projectile->namedtag->getString("Type");
                    $this->getPlugin()->spawnTNT($shooter, $projectile->getPosition(), $type);
                }
            }
        }
    }
}
