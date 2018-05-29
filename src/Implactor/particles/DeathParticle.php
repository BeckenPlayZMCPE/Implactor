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

namespace Implactor\particles;

use pocketmine\scheduler\PluginTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\level\particle\EnchantmentTableParticle;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\Location;


use Implactor\MainIR;

class DeathParticle extends PluginTask {
	
	/** @var Player */
	private $player;
	
  public function __construct(MainIR $plugin, Player $player){
    parent::__construct($plugin);
    $this->player = $player;
  }
  public function onRun(int $currentTick){
    $level = $this->player->getLevel();
    
    $r = rand(1,300);
    $g = rand(1,300);
    $b = rand(1,300);
    
    $x = $this->player->getX();
    $y = $this->player->getY();
    $z = $this->player->getZ();
    
    $center = new Vector3($x, $y, $z);
    
    $radius = 1;
    $count = 6;
    
	  $deathp = new EnchantmentTableParticle($center, $r, $g, $b, 1);
                for($yaw = 0, $y = $center->y; $y < $center->y + 4; $yaw += (M_PI * 2) / 20, $y += 1 / 20){
                  $x = -sin($yaw) + $center->x;
                  $z = cos($yaw) + $center->z;
                  $deathp->setComponents($x, $y, $z);
                  $level->addParticle($deathp);
          }
      }
}
