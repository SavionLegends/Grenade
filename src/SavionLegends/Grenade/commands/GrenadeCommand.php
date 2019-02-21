<?php


namespace SavionLegends\Grenade\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use SavionLegends\Grenade\Main;

class GrenadeCommand extends CommandClass{

    /**
     * GrenadeCommand constructor.
     * @param Main $plugin
     * @param $name
     * @param $desc
     * @param string $usage
     * @param array $aliases
     */
    public function __construct(Main $plugin, $name, $desc, string $usage, array $aliases = []){
        parent::__construct($plugin, $name, $desc, $usage, $aliases);
        $this->setPermission("grenade.command");
    }

    /**
     * @return Main
     */
    public function getPlugin(): Main{
        return parent::getPlugin();
    }

    /**
     * @return \pocketmine\Server
     */
    public function getServer(): \pocketmine\Server{
        return parent::getServer();
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return false;
        }

        if(isset($args[0]) && $args[0] === strtolower("give")){
            if(isset($args[1])){
                if(!isset(Main::$types[strtoupper($args[1])])){
                    $sender->sendMessage(TextFormat::RED."That isn't a grenade type!");
                    return false;
                }
                $type = Main::$types[strtoupper($args[1])];
                if(isset($args[2])){
                    $count = $args[2];
                    if($count <= 0){
                        $sender->sendMessage(TextFormat::RED."Count must be greater than 0!");
                        return false;
                    }
                    if(isset($args[3])){
                        $player = $this->getServer()->getPlayer($args[3]);
                        if($player !== null && $player->isOnline()){
                          $this->getPlugin()->giveGrenade($player, $count, $type);
                            $sender->sendMessage(TextFormat::YELLOW."Gave ".$player->getName()." ".$count." grenade(s)!");
                        }else{
                            $sender->sendMessage(TextFormat::RED."That player isn't online!");
                        }
                    }else{
                        if(!$sender instanceof Player){
                            $sender->sendMessage(TextFormat::RED."Please join the server to run commands!");
                            return false;
                        }
                       $this->getPlugin()->giveGrenade($sender, $count, $type);
                    }
                }else{
                    if(!$sender instanceof Player){
                        $sender->sendMessage(TextFormat::RED."Please join the server to run commands!");
                        return false;
                    }
                    $this->getPlugin()->giveGrenade($sender, 1, $type);
                }
            }else{
                $sender->sendMessage(TextFormat::RED."Usage: ".$this->getUsage());
            }
        }else{
            $sender->sendMessage(TextFormat::RED."Usage: ".$this->getUsage());
        }
        return true;
    }

}