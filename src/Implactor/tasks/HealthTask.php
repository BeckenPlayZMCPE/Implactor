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

use pocketmine\scheduler\PluginTask;

use Implactor\MainIR;

class HealthTask extends PluginTask {

    public function __construct(MainIR $plugin, $player) {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->player = $player;
    }
    
    public function onRun(int $currentTick){
        $this->plugin = $this->getOwner();
        $this->plugin->setHealthNametag($this->player);
    }
}
