<?php

/**
 * Author: DaRealAqua
 * Date: May 30, 2024
 */

namespace crate\darealaqua;

use crate\darealaqua\command\CrateCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use JsonException;

class Main extends PluginBase
{

    /** @var SingletonTrait */
    use SingletonTrait;

    /** @var Config */
    public Config $keys;

    /** @var Config */
    private Config $messages;

    /** @var Config */
    private Config $crates;

    /** @var CrateManager */
    private CrateManager $crateManager;

    /**
     * @return void
     */
    protected function onEnable(): void
    {
        self::setInstance($this);
        $this->crateManager = new CrateManager($this);
        $this->getServer()->getCommandMap()->register($this->getDescription()->getName(), new CrateCommand($this), "aquacrates");
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->config();
        $this->world();
    }

    /**
     * @return void
     */
    public function config(): void
    {
        $this->saveResource("keys.json");
        $this->saveResource("config.yml");
        $this->saveResource("messages.yml");
        $this->saveResource("crates.yml");
        $this->keys = new Config($this->getDataFolder() . "keys.json", Config::JSON);
        $this->messages = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        $this->crates = new Config($this->getDataFolder() . "crates.yml", Config::YAML);
    }

    /**
     * @return void
     */
    public function world(): void
    {
        $worldManager = $this->getServer()->getWorldManager();
        foreach ($this->getCratesCfg()->getNested("crates") as $crate => $data) {
            $world = $data["world"]["world"];
            if ($worldManager->isWorldLoaded($world) == false) {
                $worldManager->loadWorld($world);
            }
            $level = $worldManager->getWorldByName($world);
            if ($level == null) {
                $this->getServer()->shutdown();
                return;
            } else {
                $worldManager->loadWorld($world);
            }
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    protected function onDisable(): void
    {
        $this->getConfig()->save();
        $this->keys->save();
        $this->crates->save();
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->getConfig()->get("prefix");
    }

    /**
     * @return Config
     */
    public function getKeyCfg(): Config
    {
        return $this->keys;
    }

    /**
     * @return Config
     */
    public function getCratesCfg(): Config
    {
        return $this->crates;
    }

    /**
     * @return Config
     */
    public function getMessageCfg(): Config
    {
        return $this->messages;
    }

    /**
     * @return CrateManager
     */
    public function getCrateManager(): CrateManager
    {
        return $this->crateManager;
    }

}
