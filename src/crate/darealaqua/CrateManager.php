<?php

/**
 * Author: DaRealAqua
 * Date: May 30, 2024
 */

namespace crate\darealaqua;

use crate\darealaqua\task\CrateAnimationTask;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use JsonException;
use pocketmine\world\Position;

class CrateManager
{

    /** @var bool */
    private bool $runningCrate = false;

    /**
     * CrateManager constructor.
     * @param Main $plugin
     */
    public function __construct(private Main $plugin){
    }

    /**
     * @param Player $player
     * @param string $crate
     * @param int $amount
     * @return void
     * @throws JsonException
     */
    public function addKey(Player $player, string $crate, int $amount = 1): void
    {
        $keys = $this->plugin->getKeyCfg()->getNested($player->getName() . "." . strtolower($crate));
        $this->plugin->getKeyCfg()->setNested($player->getName() . "." . strtolower($crate), $keys + $amount);
        $this->plugin->getKeyCfg()->save();
    }

    /**
     * @param Player $player
     * @param string $crate
     * @param int $amount
     * @return void
     * @throws JsonException
     */
    public function takeKey(Player $player, string $crate, int $amount = 1): void
    {
        $keys = $this->plugin->getKeyCfg()->getNested($player->getName() . "." . strtolower($crate));
        $this->plugin->getKeyCfg()->setNested($player->getName() . "." . strtolower($crate), $keys - $amount);
        $this->plugin->getKeyCfg()->save();
    }

    /**
     * @param Player $player
     * @param string $crate
     * @return string
     */
    public function getKey(Player $player, string $crate): string
    {
        $keys = $this->plugin->getKeyCfg()->getNested($player->getName() . "." . strtolower($crate));
        if ($keys === null) {
            $keys = 0;
        }
        return number_format($keys);
    }

    /**
     * @param $reward
     * @return Item|null
     */
    public function addReward($reward): Item|null
    {
        if (isset($reward["count"])) {
            $item = StringToItemParser::getInstance()->parse((int)$reward["id"]) ?? LegacyStringToItemParser::getInstance()->parse((int)$reward["id"]);
            if (isset($reward["customName"])) {
                $item->setCustomName($reward["customName"]);
            }
            if (isset($reward["lore"])) {
                $item->setLore([$reward["lore"]]);
            }
            if (isset($reward["enchantId"])) {
                $item->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId((int)$reward["enchantId"]), isset($reward["enchantLevel"]) ? (int)$reward["enchantLevel"] : 1));
            }
            return $item;
        } else {
            return null;
        }
    }

    /**
     * @param Player $player
     * @param string $crate
     * @param $data
     * @return void
     * @throws JsonException
     */
    public function sendOpen(Player $player, string $crate, $data): void
    {
        $keys = $this->getKey($player, $crate);
        $messageCfg = $this->plugin->getMessageCfg();
        $config = $this->plugin->getConfig();

        // Players can't open a crate if they don't have keys!
        if ($keys <= 0) {
            $noKeyCfg = str_replace(["{prefix}", "{crate}", "{color}"], [$this->plugin->getPrefix(), $crate, $data["color"]], $messageCfg->get("no-key"));
            $this->knockback($player, $data);
            if ($config->get("settings")["message"] === false) {
                $player->sendPopup($noKeyCfg);
            } else {
                $player->sendMessage($noKeyCfg);
            }
            return;
        }

        // Players can't open a crate if they have a full inventory!
        if ($player->getInventory()->getSize() === count($player->getInventory()->getContents())) {
            $invFullCfg = str_replace(["{prefix}", "{crate}", "{color}"], [$this->plugin->getPrefix(), $crate, $data["color"]], $messageCfg->get("inv-full"));
            $this->knockback($player, $data);
            if ($config->get("settings")["message"] === false) {
                $player->sendPopup($invFullCfg);
            } else {
                $player->sendMessage($invFullCfg);
            }
            return;
        }

        // Players cannot open a crate if someone else uses it!
        if ($this->isRunningCrate() === true) {
            $inUseCfg = str_replace(["{prefix}", "{crate}", "{color}"], [$this->plugin->getPrefix(), $crate, $data["color"]], $messageCfg->get("in-use"));;
            $this->knockback($player, $data);
            if ($config->get("settings")["message"] === false) {
                $player->sendPopup($inUseCfg);
            } else {
                $player->sendMessage($inUseCfg);
            }
            return;
        }

        $this->takeKey($player, $crate);

        // Crate Animation.
        $this->plugin->getScheduler()->scheduleRepeatingTask(new CrateAnimationTask($this->plugin, $player, $crate, $data), 1);
    }

    /**
     * @param Position $position
     * @param Player $player
     * @param string $crate
     * @return void
     * @throws JsonException
     */
    public function createCrate(Position $position, Player $player, string $crate): void
    {
        $config = $this->plugin->getCratesCfg();
        $messageCfg = $this->plugin->getMessageCfg();
        $x = round($position->getX());
        $y = round($position->getY());
        $z = round($position->getZ());
        $name = $crate;
        $config->setNested("crates.{$crate}.color", "§b");
        $config->setNested("crates.{$crate}.display", "{color}" . $name . " Crate{line}§7You have {color}{key} §7" . $name . " keys");
        $config->setNested("crates.{$crate}.world.world", $player->getWorld()->getFolderName());
        $config->setNested("crates.{$crate}.world.x", $x);
        $config->setNested("crates.{$crate}.world.y", $y);
        $config->setNested("crates.{$crate}.world.z", $z);
        $customReward[] = [
            "id" => 276,
            "count" => 1,
            "customName" => "Add CustomName",
            "lore" => "Add Lore",
            "enchantId" => 9,
            "enchantLevel" => 5
        ];
        $config->setNested("crates.{$crate}.items", $customReward);
        $customCommand[] = [
            "name" => "1x Sword",
            "cmd" => "give {player} 276 1"
        ];
        $config->setNested("crates.{$crate}.commands", $customCommand);
        $config->save();
        $config->reload();
        $player->sendMessage(str_replace(["{prefix}", "{player}", "{x}", "{y}", "{z}", "{line}", "{crate}"], [$this->plugin->getPrefix(), $player->getName(), $x, $y, $z, "\n", $crate], $messageCfg->get("create")));
    }

    /**
     * @param Position $position
     * @param Player $player
     * @param string $crate
     * @return void
     * @throws JsonException
     */
    public function setCrate(Position $position, Player $player, string $crate): void
    {
        $config = $this->plugin->getCratesCfg();
        $messageCfg = $this->plugin->getMessageCfg();
        $x = round($position->getX());
        $y = round($position->getY());
        $z = round($position->getZ());
        $player->sendMessage(str_replace(["{prefix}", "{player}", "{x}", "{y}", "{z}", "{line}", "{crate}"], [$this->plugin->getPrefix(), $player->getName(), $x, $y, $z, "\n", $crate], $messageCfg->get("set")));
        $config->setNested("crates.{$crate}.world.world", $player->getWorld()->getFolderName());
        $config->setNested("crates.{$crate}.world.x", $x);
        $config->setNested("crates.{$crate}.world.y", $y);
        $config->setNested("crates.{$crate}.world.z", $z);
        $config->save();
        $config->reload();
    }

    /**
     * @param Player $player
     * @param $data
     * @return void
     */
    public function knockback(Player $player, $data): void
    {
        $position = $player->getPosition();
        $player->knockBack($position->getX() - $data["world"]["x"], $position->getZ() - $data["world"]["z"], 0.5);
    }

    /**
     * @return bool
     */
    public function isRunningCrate(): bool
    {
        return $this->runningCrate;
    }

    /**
     * @param bool $value
     */
    public function setRunningCrate(bool $value = true): void
    {
        $this->runningCrate = $value;
    }

    public const
       CREATE_MODE = 0,
        SET_MODE = 1;
    private array $createModeList = [];

    /**
     * @return array
     */
    public function getModeList(): array
    {
        return $this->createModeList;
    }

    /**
     * @param Player $player
     * @param string $mode
     * @param string $crate
     * @return void
     */
    public function setInMode(Player $player, string $mode, string $crate): void
    {
        if (!isset($this->createModeList[$mode]) && !isset($this->createModeList[$mode][$name = $player->getName()])) {
            $this->createModeList[$mode][] = $name;
            $this->createModeList[$mode][$name] = $crate;
        }
    }

    /**
     * @param Player $player
     * @param string $mode
     * @return mixed
     */
    public function getDataFromModeList(Player $player, string $mode)
    {
        return $this->createModeList[$mode][$player->getName()];
    }

    /**
     * @param string $mode
     * @return bool
     */
    public function existMode(string $mode): bool
    {
        return isset($this->createModeList[$mode]);
    }

    /**
     * @param Player $player
     * @param string $mode
     * @return bool
     */
    public function isInMode(Player $player, string $mode): bool
    {
        return isset($this->createModeList[$mode][$player->getName()]);
    }

    /**
     * @param Player $player
     * @param string $mode
     * @return void
     */
    public function removeFromMode(Player $player, string $mode): void
    {
        if (isset($this->createModeList[$mode]) && isset($this->createModeList[$mode][$name = $player->getName()])) {
            unset($this->createModeList[$mode][$name]);
        }
    }

}