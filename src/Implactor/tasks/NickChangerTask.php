<?php
/*
*
*  _____                 _            _             
* |_   _|               | |          | |            
*   | |  _ __ ___  _ __ | | __ _  ___| |_ ___  _ __ 
*   | | | '_ ` _ \| '_ \| |/ _` |/ __| __/ _ \| '__|
*  _| |_| | | | | | |_) | | (_| | (__| || (_) | |   
* |_____|_| |_| |_| .__/|_|\__,_|\___|\__\___/|_|   
*                 | |                               
*                 |_|                               
*
* Implactor (1.4.x | 1.5.x)
* A plugin with some features for Minecraft: Bedrock!
* --- = ---
*
* Team: ImpladeDeveloped
* 2018 (c) Zadezter
*
*/

declare(strict_types=1);

namespace Implactor\tasks;

use pocketmine\event\Listener;
/* EssentialsPE Required */
use EssentialsPE\Events\PlayerNickChangeEvent;

use Implactor\MainIR;

class NickChangerTask implements Listener {

    public function __construct(MainIR $plugin) {
        $this->plugin = $plugin;
    }
    
    public function onNickChange(PlayerNickChangeEvent $event) {
        $this->plugin->broadcastMessage("A Players Nick has changed!");
        $this->player = $event->getPlayer();
        $this->config = $this->plugin->getConfig()->getAll();
        if($this->config["Nametag"]["Enabled"] === true) {
            $this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new Task($this, $this->player), 1);
        }
    }

}