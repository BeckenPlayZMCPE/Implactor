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

namespace Implactor\npc\bot;

use pocketmine\entity\Entity;
use pocketmine\scheduler\PluginTask;

use Implactor\MainIR;
use Implactor\npc\bot\BotHuman;

class BotTask extends PluginTask{

	/** @var MainIR $plugin */
	/** @var Entity $entity */
	private $plugin, $entity;

	public function __construct(MainIR $plugin, Entity $entity){
		$this->plugin = $plugin;
		$this->entity = $entity;
		parent::__construct($plugin);
	}

	public function onRun(int $currentTick){
		$entity = $this->entity;

		if($entity instanceof BotHuman){
			$this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new BotSneakTask($this->plugin, $entity), 3 * 20);
			$this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new BotUnsneakTask($this->plugin, $entity), 3 * 20);
		}
	}
}
