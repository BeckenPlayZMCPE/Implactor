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

namespace Implactor;

use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\Server;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;

use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as IR;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\entity\EffectInstance;
use pocketmine\math\Vector3;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\DestroyBlockParticle as FrostBloodParticle;
use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\level\sound\DoorCrashSound;
use pocketmine\level\sound\AnvilBreakSound;
use pocketmine\level\sound\GhastShootSound;
use pocketmine\utils\Config;

use Implactor\particles\HubParticle;
use Implactor\particles\BotParticle;
use Implactor\particles\DeathParticle;
use Implactor\npc\DeathHumanEntityTask;
use Implactor\npc\DeathHumanClearEntityTask;
use Implactor\npc\bot\BotHuman;
use Implactor\npc\bot\BotTask;
use Implactor\npc\bot\BotParticleTask;
use Implactor\npc\bot\BotSneakTask;
use Implactor\npc\bot\BotUnsneakTask;

class MainIR extends PluginBase implements Listener {
	
   /** @var array $freeze */
    private $freeze = [];
   /** @var array $vanish */
    private $vanish = [];

  public function onLoad(): void{
   $this->getLogger()->info(IR::AQUA . "Loading all resources and codes on Implactor plugin...");
  }
	
  public function onEnable(): void{
         $this->getServer()->getScheduler()->scheduleRepeatingTask(new HubParticle($this, $this), 20);
	  if(!is_dir($this->getDataFolder())){
		@mkdir($this->getDataFolder());
		}
		$this->saveResource("bot.yml");
		$config = new Config($this->getDataFolder() . "bot.yml", Config::YAML);
		if($config->get("maths")){
			$this->getLogger()->info("Enabling file from configuration: - Maths....");
		}
	        $this->getLogger()->info(IR::GREEN . "Implactor plugin is now online!");
         $this->getServer()->getPluginManager()->registerEvents($this, $this);
	 Entity::registerEntity(DeathHumanEntityTask::class, true);
	 Entity::registerEntity(BotHuman::class, true);
       }
  
         public function onDisable(): void{
          $this->getLogger()->info(IR::RED . "Implactor plugin is now offline!");
          $this->getServer()->shutdown();
        }
        
         public function onPlayerLogin(PlayerLoginEvent $ev): void{
          $ev->getPlayer()->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
          $ev->getPlayer()->setHealth(40);
          $ev->getPlayer()->setMaxHealth(40);
	}
	
	public function onPlayerPreLogin(PlayerPreLoginEvent $ev) : void{
        if(!$this->getServer()->isWhitelisted($ev->getPlayer()->getName())){
            $ev->setKickMessage("§l§7[ §cNOTICE §7]\n §eThis server is currently on §fmaintenence §emode!");
            $ev->setCancelled(true);
        if(!$this->getServer()->isBanned($ev->getPlayer()->getName())){
            $ev->setKickMessage("§l§7[ §cNOTICE §7]\n §eYou got §cbanned §efrom this server!");
            $ev->setCancelled(true);
        }
     }
   }
	
	     public function onPlayerJoin(PlayerJoinEvent $ev): void{
             $player = $ev->getPlayer();
             $ev->setJoinMessage("§8[§a+§8] §a{$player->getName()}");
             $player->setHealth(40);
             $player->setMaxHealth(40);
             $player->getLevel()->addSound(new EndermanTeleportSound($player));
   }
         
          public function onHit(EntityDamageEvent $ev): void{
           if ($ev->getEntity() instanceof Player) {
            if ($ev instanceof EntityDamageByEntityEvent) {
                $ev->getEntity()->getLevel()->addParticle(new FrostBloodParticle($ev->getEntity(), Block::get(57)));
                  }
                }
              }
	
           public function onMove(PlayerMoveEvent $ev) : void{
           $player = $ev->getPlayer();
           $player->getLevel()->addParticle(new AngryVillagerParticle($player));
           $player->getLevel()->addParticle(new FlameParticle($player));
       
            if(in_array($player->getName(), $this->freeze)) $ev->setCancelled(true);
         }    
	
         public function onPlayerQuit(PlayerQuitEvent $ev): void{
         $player = $ev->getPlayer();
         $ev->setQuitMessage("§8[§c-§8] §c{$player->getName()}");   
         $player->getLevel()->addSound(new DoorCrashSound($player));
      }
  
          public function onPlayerDeath(PlayerDeathEvent $ev): void{
          $player = $ev->getPlayer();
          $this->getServer()->getScheduler()->scheduleDelayedTask(new DeathParticle($this, $player), 20);
          $player->getLevel()->addSound(new AnvilBreakSound($player));
          $player->getLevel()->addSound(new GhastShootSound($player));
         
          $nbt = new CompoundTag("", [
            new ListTag("Pos", [
                new DoubleTag("", $player->getX()),
                new DoubleTag("", $player->getY() - 1),
                new DoubleTag("", $player->getZ())
            ]),
            new ListTag("Motion", [
                new DoubleTag("", 0),
                new DoubleTag("", 0),
                new DoubleTag("", 0)
            ]),
            new ListTag("Rotation", [
                new FloatTag("", 2),
                new FloatTag("", 2)
            ])
        ]);
        $nbt->setTag($player->namedtag->getTag("Skin"));
        $npc = new DeathHumanEntityTask($player->getLevel(), $nbt);
        $npc->getDataPropertyManager()->setBlockPos(DeathHumanEntityTask::DATA_PLAYER_BED_POSITION, new Vector3($player->getX(), $player->getY(), $player->getZ()));
        $npc->setPlayerFlag(DeathHumanEntityTask::DATA_PLAYER_FLAG_SLEEP, true);
        $npc->setNameTag("§7[§cDead§7]§r\n§f" .$player->getName(). "");
        $npc->setNameTagAlwaysVisible(false);
        $npc->spawnToAll();
        $this->getServer()->getScheduler()->scheduleDelayedTask(new DeathHumanClearEntityTask($this, $npc, $player), 20);
       }
         
             public function onDamage(EntityDamageEvent $ev) : void{
             $entity = $ev->getEntity();
             if($entity instanceof Player){
            if($ev->getCause() === EntityDamageEvent::CAUSE_FALL){
                $ev->setCancelled(true);
             }
                if($ev->getCause() !== $ev::CAUSE_FALL){
                if(!$entity instanceof Player) return;
                if($entity->isCreative()) return;
                if($entity->getAllowFlight() == true){
                    $entity->setFlying(false);
                    $entity->setAllowFlight(false);
                    $entity->sendMessage("§l§7(§c!§7)§r §cYou are getting hit by a player!§e §cOh no§e...");
                    $entity->getLevel()->addParticle(new FrostBloodParticle($ev->getEntity(), Block::get(57)));      
                     if($entity instanceof DeathHumanEntityTask) $ev->setCancelled(true);
              }
          }
       }
     }
         
          public function onPlayerRespawn(PlayerRespawnEvent $ev) : void{
          $player = $ev->getPlayer();
            $title = "§l§cYOU ARE DEAD!";
             $subtitle = "§eRespawning...";
              $player->addTitle($title, $subtitle);
		  $player->setHealth(40);
                  $player->setMaxHealth(40);                  
           }
       
                 public function onEntitySpawn(EntitySpawnEvent $ev){
		           $entity = $ev->getEntity();
		            if($entity instanceof BotHuman){
			       $this->getServer()->getScheduler()->scheduleRepeatingTask(new BotTask($this, $entity), 200);
		      }
	       }
	
	                     public function spawnBot(Player $player, string $name){
		                    $nbt = Entity::createBaseNBT($player, null, 2, 2);
		                   $nbt->setTag($player->namedtag->getTag("Skin"));
		                    $npc = new BotHuman($player->getLevel(), $nbt);
		                  $npc->setNameTag("§7[§bBot§7]§r\n§f" .$name. "");
		                   $npc->setNameTagAlwaysVisible(true);
		                  $npc->spawnToAll();
		                }       
	
                              public function onPlayerChat(PlayerChatEvent $ev): void{
                              $config = new Config($this->getDataFolder() . "bot.yml", Config::YAML);
                              if($config->get("maths")){
                              $msg = $ev->getMessage();
                               $player = $ev->getPlayer();
				    $prefix = $config->get("prefix");
                                 if($msg[0] == $prefix){
				foreach($this->getServer()->getOnlinePlayers() as $p){
					if(!is_numeric($msg[1]) or !is_numeric($msg[3]) or $msg[2] !== "+" or $msg[2] !== "-" or $msg[2] !== "×" or $msg[2] !== "÷"){
						$player->sendMessage(TextFormat::GREEN . "§7[§bBot§7]§r " . TextFormat::RED . "Bot Usage§e:§c " . $prefix . " {number} + or - or × or ÷ {number}");
						$ev->setCancelled();
						return;
                                                } else {
						if($msg[2] == "+"){
							$p->sendMessage("§7[§bBot§7]§r " . IR::BLUE . $msg[1] . " plus " . $msg[3] . " equals " . (int)$msg[1] + (int)$msg[3]);
							return;
						} elseif($msg[2] == "-"){
							$p->sendMessage("§7[§bBot§7]§r " . IR::BLUE . $msg[1] . " minus " . $msg[3] . " equals " . (int)$msg[1] - (int)$msg[3]);
							return;
						} elseif($msg[2] == "×"){
							$p->sendMessage("§7[§bBot§7]§r " . IR::YELLOW . $msg[1] . " times " . $msg[3] . " equals " . (int)$msg[1] * (int)$msg[3]);
							return;
						} elseif($msg[2] == "÷"){
							$p->sendMessage("§7[§bBot§7]§r " . IR::YELLOW . $msg[1] . " divided by " . $msg[3] . " equals " . (int)$msg[1] / (int)$msg[3]);
							return;
						}
					}
					if($msg == $prefix . " 2 + 2 - 1"){
						$p->sendMessage(TextFormat::GREEN . "§7[§bSimply§7]§r " . IR::YELLOW . "2 + 2 equals 4, and minus 1 equals 3!");
						return;
                                               }
                                             }
					 }
				      }
				     }
	       
                      public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
                      if(strtolower($command->getName()) == "hub") {
                      	if($sender instanceof Player){
                       if($sender->hasPermission("implactor.hub")) {
                          $sender->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
                          $sender->addTitle("§7§l[§eHUB§7]§r", "§aReturning§f...");
                          $sender->sendMessage(IR::GRAY. "-------" .IR::WHITE. "\n Returning to hub..." .IR::GRAY. "\n-------");
                          return true;
                     }
                 }
              }
                 
                       if(strtolower($command->getName()) == "sethub") {
			       if($sender instanceof Player){
                       	if($sender->hasPermission("implactor.sethub")) {                       	   
                       	  $sender->getLevel()->setSpawnLocation($sender);
                             $sender->sendMessage(IR::YELLOW . "You have successfully set a main hub!");
                             return true;
                         }
                      }
		}
                    
                        
                        if(strtolower($command->getName()) == "fly") {
                        	if($sender instanceof Player){
                       	if($sender->hasPermission("implactor.fly")) {                     	   
                                if(!$sender->getAllowFlight()){
                                 $sender->setAllowFlight(true);
                                 $sender->sendMessage("§8§l(§a!§8)§r §7You have §aenabled §7your fly ability!");
                              }else{
                                 $sender->setAllowFlight(false);
                                 $sender->setFlying(false);
                                 $sender->sendMessage("§8§l(§c!§8)§r §7You have §cdisabled §7your fly ability!");
                                 }
                               }else{
                                 $sender->sendMessage("§cYou have no permission allowed to use §fFly §ccommand§e!");
                                 return false;
                                }
                                return true;
                              }
                           }
			      
                           if(strtolower($command->getName()) == "gmc") {
				   if($sender instanceof Player){
                       	if($sender->hasPermission("implactor.gamemode")) {                      	   
                       	   $sender->setGamemode(Player::CREATIVE);
                           $sender->sendMessage("§eChanged your gamemode to §aCreative §emode! \n\n §7- §cDo not use this command again when you're already changed...");
                           return true;
                       }
                     }
                   }
                     
                           if(strtolower($command->getName()) == "gms") {
				   if($sender instanceof Player){
                       	if($sender->hasPermission("implactor.gamemode")) {                       	   
                              $sender->setGamemode(Player::SURVIVAL); 
                              $sender->sendMessage("§eChanged your gamemode to §cSurvival §emode! \n\n §7- §cDo not use this command again when you're already changed...");
                              return true;
                       }
                     }
                  }
                   
                           if(strtolower($command->getName()) == "gma") {
				   if($sender instanceof Player){
                       	if($sender->hasPermission("implactor.gamemode")) {                     	   
                       	   $sender->setGamemode(Player::ADVENTURE);
                           $sender->sendMessage("§eChanged your gamemode to §cAdventure §emode! \n\n §7- §cDo not use this command again when you're already changed...");
                           return true;
                        }
                      }
                   }
                    
                           if(strtolower($command->getName()) == "gmspc") {
				   if($sender instanceof Player){
                       	if($sender->hasPermission("implactor.gamemode")) {
                              $sender->setGamemode(Player::SPECTATOR);
                              $sender->sendMessage("§eChanged your gamemode to §bSpectator §emode! \n\n §7- §cDo not use this command again when you're already changed...");
                              return true;
                            }
                         }
                      }
			      
                          
                           if(strtolower($command->getName()) == "nick") {
                           	if($sender instanceof Player){
                            if($sender->hasPermission("implactor.nick")){
                            if(count($args) > 0){
                            if($args[0] == "off"){
                            $sender->setDisplayName($sender->getName());
                             $sender->sendMessage("§l§8(§c!§8)§r §7You have set your nickname as §l§cdefault§r§7!");
                          }else{
                              $sender->setDisplayName($args[0]);
                            $sender->sendMessage("§l§8(§a!§8)§r §7You have set your nickname as §l§a" . $args[0] . "§7!");
                             }
                         }else{
                            $sender->sendMessage("§l§8(§6!§8)§r §cCommand usage§8:§r§7 /nick <name|off>");
                            return false;
                             }
                          }else{
                             $sender->sendMessage("§cYou have no permission allowed to use §bNick §ccommand§e!");
                              return false;
                              }
                              return true;
                           }
                         }
           
                                           if(strtolower($command->getName()) == "wild") {
                                           	if($sender instanceof Player){
                                             if($sender->hasPermission("implactor.wild")){
                                             $x = mt_rand(1, 999);
                                             $z = mt_rand(1, 999);
                                             $y = $sender->getLevel()->getHighestBlockAt($x, $z) + 1;
                                             $sender->teleport(new Position($x, $y, $z, $sender->getLevel()));
                                             $sender->addTitle("§7§l[§dWILD§7]§r", "§fRandom Teleporting...");
                                             $sender->sendMessage("§7-------\n §cTeleporting to random\n §cof §dwild zone§c... §7\n-------");
                                             return true;
                                           }
                                        }
                                    }
                                      
                                            if(strtolower($command->getName()) == "kill") {
						    if($sender instanceof Player){
                                             if($sender->hasPermission("implactor.kill")){                                            
                                            $sender->setHealth(0);
                                            $sender->sendMessage("§cMove like pain, be steady like a death!");
                                            return true;
                                          }
                                        }
                                      }
                                      
                                    if(strtolower($command->getName()) == "ping") {
                                    	if($sender instanceof Player){
                                     if($sender->hasPermission("implactor.ping")){
                                     $sender->sendMessage($sender->getPlayer()->getName(). "§a's ping status§e,");
                                     $sender->sendMessage("§b" . $sender->getPing() . "§fms §aon your connection§e!");
                                     return true;
                                  }
                              }
                          }
                              
                              if(strtolower($command->getName()) == "clearitem") {
				      if($sender instanceof Player){
                                     if($sender->hasPermission("implactor.clearinventory")){                                  
                                    $sender->getInventory()->clearAll();
                                    $sender->sendMessage("§aAll §eitems §awas cleared successfully from your inventory!");
                                    return true;
                                    }
                                 }
                            }     
				      
                                 if(strtolower($command->getName()) == "cleararmor") {
					 if($sender instanceof Player){
                                     if($sender->hasPermission("implactor.cleararmor")){                                  
                                    $sender->getArmorInventory()->clearAll();
                                    $sender->sendMessage("§eAll armors §awas cleared successfully from your body!");
                                    return true;
                                       }
				     }
                                   }
                            
                                    if(strtolower($command->getName()) == "clearall") {
					    if($sender instanceof Player){
                                     if($sender->hasPermission("implactor.clearall")){                                    	                                              
                                    $sender->getInventory()->clearAll();
                                    $sender->getArmorInventory()->clearAll();
                                    $sender->sendMessage("§aAll §eitems §aand §earmors §awas cleared successfully from yourself!");
                                    return true;
                                    }
                                 }
                              }
                       
                                     if(strtolower($command->getName()) == "heal") {
					     if($sender instanceof Player){
                                     if($sender->hasPermission("implactor.heal")){                                     	
                                     	$sender->setHealth(40);
                                         $sender->setMaxHealth(40);
                                         $sender->sendMessage("§aYour life points has been fully §ehealed!");
                                          return true;
                                     }
                                   }
                                }
                                 
                                     if(strtolower($command->getName()) == "feed") {
					     if($sender instanceof Player){
                                     if($sender->hasPermission("implactor.feed")){                                     	
                                     	$sender->setFood(20);
                                         $sender->sendMessage("§aYour hunger bar has been fully §efilled!");
                                         return true;
                                     }
                                  }
                                }
                                
                                    if(strtolower($command->getName()) == "ihelp") {
                                    	if($sender instanceof Player){
                                     if($sender->hasPermission("implactor.command.help")){
                                            $sender->sendMessage("§b--( §eImplactor §aHelp §b)--");
                                            $sender->sendMessage("§e/ihelp §9- §fImplactor Command List!");
                                            $sender->sendMessage("§e/iabout §9- §fAbout Implactor plugin!");
                                            $sender->sendMessage("§e/ping §9- §fCheck your ping status on server!");
                                            $sender->sendMessage("§e/feed §9- §fFeed yourself when on hunger!");
                                            $sender->sendMessage("§e/heal §9- §fHeal yourself when on emergency!");
                                            $sender->sendMessage("§e/gms §9- §fChange your gamemode to §cSurvival §fmode!");
                                            $sender->sendMessage("§e/gmc §9- §fChange your gamemode to §aCreative §fmode!");
                                            $sender->sendMessage("§e/gma §9- §fChange your gamemode to §cAdventure §fmode!");
                                            $sender->sendMessage("§e/gmspc §9- §fChange your gamemode to §bSpectator §fmode!");
                                            $sender->sendMessage("§e/hub §9- §fTeleport/Return To Hub!");
                                            $sender->sendMessage("§e/sethub §9- §fSet the main hub location point!");
                                            $sender->sendMessage("§e/fly §9- §fTurn on/off the fly ability!");
                                            $sender->sendMessage("§e/kill §9- §fKill yourself!");
                                            $sender->sendMessage("§e/wild §9- §fTeleport to the random wild zone!");
                                            $sender->sendMessage("§e/clearitem §9- §fClear your items from your inventory!");
                                            $sender->sendMessage("§e/cleararmor §9- §fClear your armor from your body!");
                                            $sender->sendMessage("§e/clearall §9- §fClear all items/armors from your inventory and body!");
                                            $sender->sendMessage("§e/nick §9- §fSet your nickname or default!");
					     $sender->sendMessage("§e/freeze §9- §bFreeze §fyourself or others will make you frozen!");
					     $sender->sendMessage("§e/vanish §9- §6Vanish §fyourself or others will make you invisible!");
					     $sender->sendMessage("§e/bot §9- §fSpawn your own §cbot §fhuman!");
					     $sender->sendMessage("§e/icast §9- §bBroadcast §fmessage to all players with §dImplacast!");		     
                                            return true;
                                           }
                                         }                                             
                                      }
			      
			                     if(strtolower($command->getName()) == "iabout") {
                                           	if($sender instanceof Player){
                                           if($sender->hasPermission("implactor.command.about")){
                                             $sender->sendMessage("§b--§a[§d Implactor §a| §bAbout §a]§b--");
                                             $sender->sendMessage("§aA plugin with having some features!");
                                             $sender->sendMessage("- §eMade by Zadezter§r \n- §fCreated on §c23 May 2018");
                                             return true;
                                             }
                                           }
                                          }
                                      
			                  if(strtolower($command->getName()) === "vanish"){
                                         if(!$sender instanceof Player){
                                       $sender->sendMessage("Please use Implactor command in-game server!");
                                       return false;
                                    }
                                      if(!$sender->hasPermission("implactor.vanish")){
                                      $sender->sendMessage("§cYou have no permission allowed to use §bFreeze §ccommand§e!");
                                      return false;
                                  }
                                    if(empty($args[0])){
                           if(!in_array($sender->getName(), $this->vanish)){
                    $this->vanish[] = $sender->getName();
                    $sender->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
                    $sender->setNameTagVisible(false);
                    $sender->sendMessage("§bYou are now §fvanished!");
                   }elseif(in_array($sender->getName(), $this->vanish)){
                    unset($this->vanish[array_search($sender->getName(), $this->vanish)]);
                    $sender->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, false);
                    $sender->setNameTagVisible(true);
                    $sender->sendMessage("§bYou are no longer §fvanished!");
                }
                return false;
               }
                if($this->getServer()->getPlayer($args[0])){
                $player = $this->getServer()->getPlayer($args[0]);
                if(!in_array($player->getName(), $this->vanish)){
                    $this->vanish[] = $player->getName();
                    $player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
                    $player->setNameTagVisible(false);
                    $player->sendMessage("§bYou are now §fvanished!");
                    $sender->sendMessage("§eYou have successfully §fvanished " . IR::GREEN . $player->getName() . "");
                }elseif(in_array($player->getName(), $this->vanish)){
                    unset($this->vanish[array_search($player->getName(), $this->vanish)]);
                    $player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, false);
                    $player->setNameTagVisible(true);
                    $player->sendMessage("§bYou are no longer §fvanished!");
                    $sender->sendMessage("§eYou have successfully §fun-vanished " . IR::GRREN . $player->getName() . "");
                  }
               }else{
                $sender->sendMessage("§cPlayer not found in server");
                return false;
              }
              return true;
          }
			      
			                    if(strtolower($command->getName()) === "freeze"){
                                           if(!$sender instanceof Player){
                                          $sender->sendMessage("§cPlease use Implactor command in-game server!");
                                          return false;
                                        }
                                          if(!$sender->hasPermission("implactor.freeze")){
                                           $sender->sendMessage("§cYou have no permission allowed to use §bFreeze §ccommand§e!");
                                           return false;
                                         }
                                          if(empty($args[0])){
                                          $sender->sendMessage("§8§l(§6!§8)§r §cCommand Usage§e:§r §b/freeze <player>");
                                          return false;
                                         }
                                    if($this->getServer()->getPlayer($args[0])){
                                    $player = $this->getServer()->getPlayer($args[0]);
                                  if(!in_array($player->getName(), $this->freeze)){
                                  $this->freeze[] = $player->getName();
                                  $player->sendMessage(IR::AQUA . "You are now frozen player!");
                                  $sender->sendMessage(IR::AQUA . "You have frozen " . $player->getName());
                                 }elseif(in_array($player->getName(), $this->freeze)){
                                   unset($this->freeze[array_search($player->getName(), $this->freeze)]);
                                   $player->sendMessage(IR::AQUA . "You are no longer frozen player!");
                                   $sender->sendMessage(IR::AQUA . "You have unfrozen " . $player->getName());
                                  }
                               }else{
                                $sender->sendMessage("§cPlayer not found in server!");
                                return false;
                               }
                               return true;
                         }
                         
                           if(strtolower($command->getName()) == "bot") {
                               if($sender instanceof Player){
                           if($sender->hasPermission("implactor.bot")){
			          if(count($args) < 1){
			          $sender->sendMessage("§l§8(§6!§8)§r §cCommand usage§8:§r§7 /bot <name>");
			          return false;
		           }
		          $this->spawnBot($sender, $args[0]);
		          $sender->sendMessage("§eSpawned §bbot §enamed§c:§r " . $args[0]);
		          return true;
	               }
	              }
                    }
			      
                          if(strtolower($command->getName()) == "icast") {
                               if($sender instanceof Player){
                           if($sender->hasPermission("implactor.broadcast")){
                             if(count($args) < 1){
                             $sender->sendMessage("§8(§6!§8)§r §cCommand usage§8:§r§7 /icast <message>");
                             return false;
                           }
                             $sender->getServer()->broadcastMessage("§7[§bImplacast§7] §b" . IR::YELLOW . implode(" ", $args));
                             return true;
                           }
                        }
                       }
                      }
                    }
