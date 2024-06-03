<?php

/**
 * Author: DaRealAqua
 * Date: May 30, 2024
 */

namespace crate\darealaqua\command;

use crate\darealaqua\CrateManager;
use crate\darealaqua\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;
use pocketmine\Server;
use JsonException;

class CrateCommand extends Command implements PluginOwned
{
    /** @var string */
    private const
        ADD_KEY_USAGE = "/crate addkey <player> <crate> [amount...]",
        REMOVE_KEY_USAGE = "/crate removekey <player> <crate> [amount...]",
        SET_KEY_USAGE = "/crate set|replace <crate>",
        CREATE_CRATE_USAGE = "/crate create <name>",
        KEY_ALL_USAGE = "/crate keyall <crate> [amount]",
        PERMISSION = "aquacrates.command";

    /**
     * CrateCommand constructor.
     * @param Main $plugin
     */
    public function __construct(private Main $plugin){
        $this->setPermission(self::PERMISSION);
        $this->setDescription("Crates system.");
        $this->setAliases(["crate"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     * @throws JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $msgConfig = $this->getPlugin()->getMessageCfg();
        if (!$sender->hasPermission(self::PERMISSION)) {
            $sender->sendMessage(str_replace(["{prefix}", "{usage}", "{line}"], [$this->getPlugin()->getPrefix(), $this->getUsage(), "\n"], $msgConfig->get("no-perm")));
            return;
        }
        if (!isset($args[0])) {
            $sender->sendMessage(str_replace(["{prefix}", "{usage}", "{line}"], [$this->getPlugin()->getPrefix(), $this->getUsage(), "\n"], $msgConfig->get("usage")));
            var_dump($this->getPlugin()->getCrateManager()->getModeList());
            return;
        }
        $list = [];
        foreach ($this->getPlugin()->getCratesCfg()->get("crates") as $crate => $data) {
            $list[] = $crate;
        }
        $crateManager = $this->getPlugin()->getCrateManager();
        $prefix = $this->getPlugin()->getPrefix();
        switch ($args[0]) {
            case "help":
                $sender->sendMessage(str_replace(["{prefix}", "{usageAdd}", "{usageRemove}", "{usageSet}", "{usageCreate}", "{usageKeyAll}", "{line}"], [$prefix, self::ADD_KEY_USAGE, self::REMOVE_KEY_USAGE, self::SET_KEY_USAGE, self::CREATE_CRATE_USAGE, self::KEY_ALL_USAGE, "\n"], $msgConfig->get("help")));
                break;
            case "list":
                $msg = [];
                foreach ($this->getPlugin()->getCratesCfg()->get("crates") as $crate => $data) {
                    $world = $data["world"];
                    $msg[] = $data["color"] . $crate . C::RESET . C::GRAY . " - " . $world["world"] . C::GREEN . " (" . $world["x"] . ", " . $world["y"] . ", " . $world["z"] . ")";
                }
                $sender->sendMessage(str_replace(["{prefix}", "{line}", "{crates}"], [$prefix, "\n", implode("\n", $msg)], $msgConfig->get("list")));
                break;
            case "keyall":
                if (!isset($args[1])) {
                    $sender->sendMessage(str_replace(["{prefix}", "{usage}", "{line}"], [$prefix, self::KEY_ALL_USAGE, "\n"], $msgConfig->get("usage-keyall")));
                    return;
                }
                if (!in_array($args[1], $list)) {
                    $sender->sendMessage(str_replace(["{prefix}", "{crates}", "{line}"], [$prefix, implode(", ", $list), "\n"], $msgConfig->get("crate-not-found")));
                    return;
                }
                if(!isset($args[2])){
                    $sender->sendMessage(str_replace(["{prefix}", "{line}"], [$prefix, "\n"], $msgConfig->get("not-numeric")));
                    return;
                }
                $amount = (int)$args[2];
                if ($amount <= 0 || $amount > 9999999999999999999999) {
                    $sender->sendMessage(str_replace(["{prefix}", "{line}"], [$prefix, "\n"], $msgConfig->get("not-numeric")));
                    return;
                }
                $sender->sendMessage(str_replace(["{prefix}", "{amount}", "{crate}", "{line}"], [$prefix, $amount, $args[1], "\n"], $msgConfig->get("keyall-giver")));
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    if ($player instanceof Player) {
                        $player->sendMessage(str_replace(["{prefix}", "{giver}", "{amount}", "{crate}", "{line}"], [$prefix, $sender->getName(), (int)number_format($amount), $args[1], "\n"], $msgConfig->get("keyall-receiver")));
                        $crateManager->addKey($player, $args[1], $amount);
                    }
                }
                break;
            case "addkey":
                if (!isset($args[1])) {
                    $sender->sendMessage(str_replace(["{prefix}", "{usage}", "{line}"], [$prefix, self::ADD_KEY_USAGE, "\n"], $msgConfig->get("usage-addkeys")));
                    return;
                }
                $player = $this->getPlugin()->getServer()->getPlayerExact($args[1]);
                if (!$player instanceof Player) {
                    $sender->sendMessage(str_replace(["{prefix}", "{line}"], [$prefix, "\n"], $msgConfig->get("player-not-found")));
                    return;
                }
                if (!isset($args[2]) || !in_array($args[2], $list)) {
                    $sender->sendMessage(str_replace(["{prefix}", "{crates}", "{line}"], [$prefix, implode(", ", $list), "\n"], $msgConfig->get("crate-not-found")));
                    return;
                }
                if(!isset($args[3])){
                    $sender->sendMessage(str_replace(["{prefix}", "{line}"], [$prefix, "\n"], $msgConfig->get("not-numeric")));
                    return;
                }
                $amount = (int)$args[3];
                if ($amount <= 0 || $amount > 9999999999999999999999) {
                    $sender->sendMessage(str_replace(["{prefix}", "{line}"], [$prefix, "\n"], $msgConfig->get("not-numeric")));
                    return;
                }
                $sender->sendMessage(str_replace(["{prefix}", "{player}", "{amount}", "{crate}", "{line}"], [$prefix, $player->getName(), (int)number_format($amount), $args[2], "\n"], $msgConfig->get("addkeys-giver")));
                $player->sendMessage(str_replace(["{prefix}", "{player}", "{amount}", "{crate}", "{line}"], [$prefix, $player->getName(), (int)number_format($amount), $args[2], "\n"], $msgConfig->get("addkeys-receiver")));
                $crateManager->addKey($player, $args[2], $amount);
                break;
            case "removekey":
            case "rmkey":
                if (!isset($args[1])) {
                    $sender->sendMessage(str_replace(["{prefix}", "{usage}", "{line}"], [$prefix, self::REMOVE_KEY_USAGE, "\n"], $msgConfig->get("usage-rmkeys")));
                    return;
                }
                $receiver = $this->getPlugin()->getServer()->getPlayerExact($args[1]);
                if (!$receiver instanceof Player) {
                    $sender->sendMessage(str_replace(["{prefix}", "{line}"], [$prefix, "\n"], $msgConfig->get("player-not-found")));
                    return;
                }
                if (!isset($args[2]) or !in_array($args[2], $list)) {
                    $sender->sendMessage(str_replace(["{prefix}", "{crates}", "{line}"], [$prefix, implode(", ", $list), "\n"], $msgConfig->get("crate-not-found")));
                    return;
                }
                $amount = (int)$args[3];
                if (!isset($args[3]) || $amount <= 0 || $amount > 9999999999999999999999) {
                    $sender->sendMessage($msgConfig->get("not-numeric"));
                    return;
                }
                $sender->sendMessage(str_replace(["{prefix}", "{player}", "{amount}", "{crate}", "{line}"], [$prefix, $receiver->getName(), (int)number_format($amount), $args[2], "\n"], $msgConfig->get("rmkeys-giver")));
                $receiver->sendMessage(str_replace(["{prefix}", "{player}", "{amount}", "{crate}", "{line}"], [$prefix, $receiver->getName(), (int)number_format($amount), $args[2], "\n"], $msgConfig->get("rmkeys-receiver")));
                $crateManager->takeKey($receiver, $args[2], $amount);
                break;
            case "set":
            case "replace":
                if (!$sender instanceof Player) {
                    $sender->sendMessage(str_replace(["{prefix}", "{line}"], [$prefix, "\n"], $msgConfig->get("in-game")));
                    return;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage(str_replace(["{prefix}", "{usage}", "{line}"], [$prefix, self::SET_KEY_USAGE, "\n"], $msgConfig->get("usage-set")));
                    return;
                }
                if (!in_array($args[1], $list)) {
                    $sender->sendMessage(str_replace(["{prefix}", "{crates}", "{line}"], [$prefix, implode(", ", $list), "\n"], $msgConfig->get("crate-not-found")));
                    return;
                }
                if (!$crateManager->isInMode($sender, CrateManager::SET_MODE)) {
                    $sender->sendMessage("§2You have just entered set mode, please click where you want to set/replace the crate position!");
                    $crateManager->setInMode($sender, CrateManager::SET_MODE, $args[1]);
                } else {
                    $crateManager->removeFromMode($sender, CrateManager::SET_MODE);
                }
                break;
            case "create":
                if (!$sender instanceof Player) {
                    $sender->sendMessage(str_replace(["{prefix}", "{line}"], [$prefix, "\n"], $msgConfig->get("in-game")));
                    return;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage(str_replace(["{prefix}", "{usage}", "{line}"], [$prefix, self::CREATE_CRATE_USAGE, "\n"], $msgConfig->get("usage-create")));
                    return;
                }
                if (in_array($args[1], $list)) {
                    $sender->sendMessage(str_replace(["{prefix}", "{crate}", "{line}"], [$prefix, $args[1], "\n"], $msgConfig->get("already-registered")));
                    return;
                }
                if (!$crateManager->isInMode($sender, CrateManager::CREATE_MODE)) {
                    $sender->sendMessage("§aYou have just entered crate mode, please click where you want to place the crate!");
                    $crateManager->setInMode($sender, CrateManager::CREATE_MODE, $args[1]);
                } else {
                    $crateManager->removeFromMode($sender, CrateManager::CREATE_MODE);
                }
                break;
            default:
                $sender->sendMessage(str_replace(["{prefix}", "{usage}", "{line}"], [$prefix, $this->getUsage(), "\n"], $msgConfig->get("usage")));
        }
    }

    /**
     * @return Main
     */
    public function getPlugin(): Main
    {
        return $this->plugin;
    }

}
