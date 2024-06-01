<?php

/**
 * Author: DaRealAqua
 * Date: May 30, 2024
 */

namespace crate\darealaqua\task;

use crate\darealaqua\Main;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\particle\FloatingTextParticle;

class CrateAnimationTask extends Task
{

    /** @var FloatingTextParticle */
    private FloatingTextParticle $floatingText;

    /** @var Vector3 */
    private Vector3 $position;

    /** @var int */
    private int $runs = 0;

    /**
     * @param Main $plugin
     * @param Player $player
     * @param string $crate
     * @param array $data
     */
    public function __construct(
        private Main   $plugin,
        private Player $player,
        private string $crate,
        private array  $data
    )
    {
        $crate = $plugin->getCrateManager();
        $crate->setRunningCrate();
    }

    /**
     * @return void
     */
    public function onRun(): void
    {
        if ($this->player->isClosed()) {
            $this->getHandler()->cancel();
            return;
        }
        ++$this->runs;

        if ($this->runs === 1) {
            $data = $this->getData();
            $crate = $this->getCrate();
            $crateConfig = $this->getPlugin()->getCratesCfg();

            if ($crateConfig->get("reward") === "item") {
                $rewards = $data["items"];
                $reward = array_rand($rewards);
                $reward = $rewards[$reward];
                $item = $this->getPlugin()->getCrateManager()->addReward($reward);
                $this->getPlayer()->getInventory()->addItem($item);
                $name = $item->getCount() . "x " . $item->getVanillaName();
                $this->spawnRewardText($name, $crate, $data);

            } else if ($crateConfig->get("reward") === "command") {
                $rewards = $data["commands"];
                $reward = array_rand($rewards);
                $reward = $rewards[$reward];
                Server::getInstance()->dispatchCommand(new ConsoleCommandSender($server = Server::getInstance(), $server->getLanguage()), str_replace("{player}", $this->player->getName(), $reward["cmd"]));
                $name = $reward["name"];
                $this->spawnRewardText($name, $crate, $data);

            } else if ($crateConfig->get("reward") === "random") {
                $this->random();
            }

            $this->updateParticles($this->getPosition());
            return;
        }

        if ($this->runs >= 20) {
            $this->getFloatingText()->setInvisible();
            $this->updateParticles($this->getPosition());
            $this->getPlugin()->getCrateManager()->setRunningCrate(false);
            $this->getHandler()->cancel();
        }
    }

    /**
     * @return void
     */
    public function random(): void
    {
        $data = $this->getData();
        $crate = $this->getCrate();
        $random = mt_rand(0, 1);
        if ($random == 0) {
            $rewards = $data["items"];
            $reward = array_rand($rewards);
            $reward = $rewards[$reward];
            $item = $this->getPlugin()->getCrateManager()->addReward($reward);
            $this->getPlayer()->getInventory()->addItem($item);
            $name = $item->getCount() . "x " . ($item->hasCustomName() ? $item->getCustomName() : $item->getVanillaName());
            $this->spawnRewardText($name, $crate, $data);
        } elseif ($random == 1) {
            $rewards = $data["commands"];
            $reward = array_rand($rewards);
            $reward = $rewards[$reward];
            Server::getInstance()->dispatchCommand(new ConsoleCommandSender($server = Server::getInstance(), $server->getLanguage()), str_replace("{player}", $this->player->getName(), $reward["cmd"]));
            $name = $reward["name"];
            $this->spawnRewardText($name, $crate, $data);
        }
    }

    /**
     * @param $name
     * @param $crate
     * @param $data
     * @return void
     */
    public function spawnRewardText($name, $crate, $data): void
    {
        $msgConfig = $this->getPlugin()->getMessageCfg();
        $config = $this->getPlugin()->getConfig();
        $this->position = new Vector3($data["world"]["x"] + 0.5, $data["world"]["y"] + 2, $data["world"]["z"] + 0.5);
        $this->sendReward($this->getPosition(),
            str_replace(["{prefix}", "{reward}", "{crate}", "{color}"], [$this->getPlugin()->getPrefix(), $name, $crate, $data["color"]], $msgConfig->get("reward")),
            str_replace(["{prefix}", "{reward}", "{crate}", "{color}"], [$this->getPlugin()->getPrefix(), $name, $crate, $data["color"]], $config->get("show-text")),
            $data
        );
    }

    /**
     * @param Vector3 $position
     * @param string $rewardText
     * @param string $crateText
     * @param array $data
     * @return void
     */
    public function sendReward(Vector3 $position, string $rewardText, string $crateText, array $data): void
    {
        $cfg = $this->getPlugin()->getConfig();
        if ($cfg->get("settings")["message"] === false) {
            $this->getPlayer()->sendPopup($rewardText);
        } else {
            $this->getPlayer()->sendMessage($rewardText);
        }
        $this->floatingText = new FloatingTextParticle($crateText);
        $level = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]["world"]);
        $level->addParticle($position, $this->floatingText, [$this->getPlayer()]);
    }

    /**
     * @param Vector3 $position
     * @return void
     */
    private function updateParticles(Vector3 $position): void
    {
        foreach ($this->getFloatingText()->encode($position) as $packet) {
            $this->player->getNetworkSession()->sendDataPacket($packet);
        }
    }

    /**
     * @return Main
     */
    public function getPlugin(): Main
    {
        return $this->plugin;
    }

    /**
     * @return string
     */
    public function getCrate(): string
    {
        return $this->crate;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return FloatingTextParticle
     */
    public function getFloatingText(): FloatingTextParticle
    {
        return $this->floatingText;
    }

    /**
     * @return Vector3
     */
    public function getPosition(): Vector3
    {
        return $this->position;
    }

}

