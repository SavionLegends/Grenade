<?php


namespace SavionLegends\Grenade\commands;


use pocketmine\command\Command;
use pocketmine\command\CommandMap;
use pocketmine\command\CommandSender;
use SavionLegends\Grenade\Main;

class CommandClass extends Command{

    private $plugin, $server;

    /**
     * CommandClass constructor.
     * @param Main $plugin
     * @param $name
     * @param $desc
     * @param string $usage
     * @param array $aliases
     */
    public function __construct(Main $plugin, $name, $desc, string $usage, $aliases = []){
        parent::__construct($name, $desc, $usage, (array)$aliases);
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
     * @param Main $main
     * @param CommandMap $map
     */
    public static function registerAll(Main $main, CommandMap $map){
        $map->registerAll("grenade",
            [new GrenadeCommand($main, "grenade", "Grenade command!", "/grenade give (count) (player)")]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(parent::testPermission($sender) === false){
            return false;
        }
        return true;
    }

}