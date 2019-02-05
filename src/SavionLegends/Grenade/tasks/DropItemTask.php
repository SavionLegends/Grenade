<?php


namespace SavionLegends\Grenade\tasks;


use pocketmine\entity\Entity;
use pocketmine\scheduler\Task;
use SavionLegends\Grenade\Main;

class DropItemTask extends Task{

    private $plugin, $server;

    /**
     * DropItemTask constructor.
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
     * @param int $currentTick
     */
    public function onRun(int $currentTick){
        foreach(Main::$dropItems as $entity){
            if($entity instanceof Entity){
                if($entity->isAlive() and !$entity->isClosed()){
                    $entity->close();
                }
            }
        }
    }

}