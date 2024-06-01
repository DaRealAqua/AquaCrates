<?php

/**
 * Author: DaRealAqua
 * Date: May 30, 2024
 */

namespace crate\darealaqua;

use crate\darealaqua\task\CrateDisplayTask;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use JsonException;

class EventListener implements Listener
{

    /**
     * EventListener constructor.
     * @param Main $plugin
     */
    public function __construct(private Main $plugin){
    }

    /**
     * @param PlayerInteractEvent $event
     * @return void
     * @throws JsonException
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if (!$player instanceof Player) {
            return;
        }
        if ($event->isCancelled()) {
            return;
        }
        $manager = $this->plugin->getCrateManager();
        if (!empty($manager->getModeList()) && !empty($manager->existMode(CrateManager::CREATE_MODE)) && $manager->isInMode($player, CrateManager::CREATE_MODE)) {
            $crate = $manager->getDataFromModeList($player, CrateManager::CREATE_MODE);
            $manager->createCrate($event->getBlock()->getPosition(), $player, $crate);
            $manager->removeFromMode($player, CrateManager::CREATE_MODE);
            $event->cancel();
        }
        if (!empty($manager->getModeList()) && !empty($manager->existMode(CrateManager::SET_MODE)) && $manager->isInMode($player, CrateManager::SET_MODE)) {
            $crate = $manager->getDataFromModeList($player, CrateManager::SET_MODE);
            $manager->setCrate($event->getBlock()->getPosition(), $player, $crate);
            $manager->removeFromMode($player, CrateManager::SET_MODE);
            $event->cancel();
        }
        if ($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            foreach ($this->plugin->getCratesCfg()->getNested("crates") as $crate => $data) {
                if (in_array($player->getWorld()->getFolderName(), [$data["world"]["world"]])) {
                    if ($block->getPosition()->x == $data["world"]["x"] && $block->getPosition()->y == $data["world"]["y"] && $block->getPosition()->z == $data["world"]["z"]) {
                        $this->plugin->getCrateManager()->sendOpen($player, $crate, $data);
                        $event->cancel();
                    }
                }
            }
        }
    }

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBlockBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if (!$player instanceof Player) {
            return;
        }
        foreach ($this->plugin->getCratesCfg()->getNested("crates") as $crate => $data) {
            if (in_array($player->getWorld()->getFolderName(), [$data["world"]["world"]])) {
                if ($block->getPosition()->x == $data["world"]["x"] && $block->getPosition()->y == $data["world"]["y"] && $block->getPosition()->z == $data["world"]["z"]) {
                    $messageCfg = $this->plugin->getMessageCfg();
                    $player->sendMessage(str_replace(["{prefix}", "{crate}", "{color}", "{line}"], [$this->plugin->getPrefix(), $crate, $data["color"], "\n"], $messageCfg->get("break")));
                    $event->cancel();
                }
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $config = $this->plugin->getConfig();
        $this->plugin->getScheduler()->scheduleRepeatingTask(new CrateDisplayTask($this->plugin, $player), 20 * $config->get("update-floatingText"));
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof Player) {
            return;
        }
        $manager = $this->plugin->getCrateManager();
        if ($manager->isInMode($player, CrateManager::CREATE_MODE)) {
            $manager->removeFromMode($player, CrateManager::CREATE_MODE);
        }
        if ($manager->isInMode($player, CrateManager::SET_MODE)) {
            $manager->removeFromMode($player, CrateManager::SET_MODE);
        }
    }

}
