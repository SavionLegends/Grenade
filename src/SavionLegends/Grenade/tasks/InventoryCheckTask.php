<?php


namespace SavionLegends\Grenade\tasks;


use pocketmine\item\Item;
use pocketmine\scheduler\Task;
use SavionLegends\Grenade\Main;

class InventoryCheckTask extends Task{

    private $plugin, $server;

    /**
     * InventoryCheckTask constructor.
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
        foreach($this->getServer()->getOnlinePlayers() as $player){
            $inventory = $player->getInventory();
            if($inventory->contains(Item::get(Item::EGG, -1)) or $inventory->contains(Item::get(Item::EGG, -2))){
                if(isset(Main::$usingGrenade[$player->getName()])){
                    Main::$usingGrenade[$player->getName()]["Count"] = 1;
                }else{
                    Main::$usingGrenade[$player->getName()]["Count"] = 1;
                }
            }else{
                if(isset(Main::$usingGrenade[$player->getName()])){
                    unset(Main::$usingGrenade[$player->getName()]);
                }
            }
        }
    }

}
