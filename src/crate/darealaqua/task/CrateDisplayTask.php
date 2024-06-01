<?php

/**
 * Author: DaRealAqua
 * Date: May 30, 2024
 */

namespace crate\darealaqua\task;

use crate\darealaqua\Main;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\particle\FloatingTextParticle;

class CrateDisplayTask extends Task
{

    /** @var array */
    public array $floatingTexts = [];

    /**
     * CrateDisplayTask constructor.
     * @param Main $plugin
     * @param Player $player
     */
    public function __construct(
        private Main   $plugin,
        private Player $player
    )
    {
    }

    /**
     * @return void
     */
    public function onRun(): void
    {
        foreach ($this->plugin->getCratesCfg()->get("crates") as $crate => $data) {
            $key = $this->plugin->getCrateManager()->getKey($this->player, $crate);
            $crateTitle = str_replace(["{key}", "{line}", "{color}"], [$key, "\n", $data["color"]], $data["display"]);
            if (!isset($this->floatingTexts[$data["world"]["x"] . "_" . $data["world"]["y"] . "_" . $data["world"]["z"]])) {
                $this->floatingTexts[$data["world"]["x"] . "_" . $data["world"]["y"] . "_" . $data["world"]["z"]] = new FloatingTextParticle($crateTitle);
            }
            $this->floatingTexts[$data["world"]["x"] . "_" . $data["world"]["y"] . "_" . $data["world"]["z"]]->setText($crateTitle);
            $level = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]["world"]);
            if ($level->getFolderName()) $level->addParticle(
                new Vector3($data["world"]["x"] + 0.5, $data["world"]["y"] + 1, $data["world"]["z"] + 0.5),
                $this->floatingTexts[$data["world"]["x"] . "_" . $data["world"]["y"] . "_" . $data["world"]["z"]],
                [$this->getPlayer()]
            );
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
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

}

