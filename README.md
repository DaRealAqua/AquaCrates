# AquaCrates
### Description
AquaCrates is a plugin that allows users create unlimited crates. Create crates in-game or by using the command /crate create <name>.

### Features
- configurable config / crates
- customizable messages
- support worlds
- rewards
- items
- commands
- floatingText

### Installation
To install AquaCrates, simply follow these steps:

Download the latest version of AquaCrates from the Releases page on GitHub / poggit.
Place the downloaded .phar file into your PocketMine-MP plugins directory.
Start your PocketMine-MP server and enjoy.

### Configuration
AquaCrates can be configured by editing the ``config.yml`` file in the plugin's directory or using the command /crate. Here you can see what can be configured:
## Config
```php
# AquaCrates Configuration
# Author: @DaRealAqua
# Twitter: @DaRealAqua_

# Time when Text's refresh.
# Recommended: '3' seconds!
update-floatingText: 3

# Prefix.
prefix: "§8[§bAquaCrates§8]§r§7"

# Settings
settings:
  # Crate Messages
  # Popup => false | Message => true
  message: true

# View Reward when you open a Crate.
show-text: "{color}{reward}"
```
## Crates
```php
# Enchantments ID List:
# Protection => 0
# Fire Protection => 1
# Feather Falling => 2
# Blast Protection => 3
# Projectile Protection => 4
# Thorns => 5
# Respiration => 6
# Aqua Affinity => 8
# Sharpness => 9
# Smite => 10
# Bane of Arthropods => 11
# Knockback => 12
# Fire Aspect => 13
# Looting => 14
# Efficiency => 15
# Silk Touch => 16
# Unbreaking => 17
# Fortune => 18
# Power => 19
# Punch => 20
# Flame => 21
# Infinity => 22

# Reward Types: item, command or random
reward: random

# Registered Crate's List.
# Create a new 'CRATE' using the example above.
crates:
  Aqua:
    color: "§b"
    display: "{color}Aqua Crate{line}§7You have {color}{key} §7Orion keys"
    world:
      world: "world"
      x: 1
      y: 5
      z: 1
    items:
      - id: 222
        count: 64
      - id: 255
        count: 64
      - id: 276
        count: 1
      - id: 276
        count: 1
        customName: "§r§l§6Orion Sword§r"
        lore: "§7This is the§6 most powerful §7sword!"
        enchantId: 9
        enchantLevel: 2
    commands:
      - name: "§6x64 Stone"
        cmd: "give {player} 1 64"
      - name: "§ax64 Grass"
        cmd: "give {player} 2 64"
      - name: "§bx1 Diamond Sword"
        cmd: "give {player} 276 1"
```
## Messages
```php
no-perm: "{prefix} §7You don't have permission to use this command!"
in-game: "{prefix} §7Use this command in-game!"
list: "§l§bCrates List{line}§c{crates}"
help: "§8-------------§l§bAquaCrates Help§r§8-------------{line}{line} §e- {usageAdd}{line} §e- {usageRemove}{line} §e- {usageSet}{line} §e- {usageCreate}{line} §e- {usageKeyAll}{line}{line}§8-------------§l§bAquaCrates Help§r§8-------------"
usage: "{prefix} {usage}"
usage-addkeys: "{prefix} §7{usage}"
usage-rmkeys: "{prefix} §7{usage}"
usage-set: "{prefix} §7{usage}"
usage-create: "{prefix} §7{usage}"
usage-keyall: "{prefix} §7{usage}"
player-not-found: "{prefix} §7Player not Found!"
crate-not-found: "{prefix} Crate not Found!{line}§7Registered Crates: §a{crates}"
not-numeric: "{prefix} §7Amount is not numeric!"
reward: "{prefix} §7You received §a{reward} §7from {color}{crate} §7Crate."
no-key: "{prefix} §7You have no key to open {color}{crate} §7Crate!"
inv-full: "{prefix} §7Your inventory is full, make room to use the {color}{crate} §7Crate!"
in-use: "{prefix} §7The {color}{crate} §7Crate is already used at the moment!"
already-registered: "{prefix} §7The §c{crate} §7Crate is already registered!"
addkeys-giver: "{prefix} §7You gave §a{player} +{amount}x {crate} §7Crate Keys."
addkeys-receiver: "{prefix} §7You received §a{player} +{amount}x {crate} §7Crate Keys."
rmkeys-giver: "{prefix} §7You removed §c{player} -{amount}x {crate} §7Crate Keys."
rmkeys-receiver: "{prefix} §7You lost §c{player} -{amount}x {crate} §7Crate Keys."
create: "{prefix} §7You have successfully created §a{crate} §7Crate, the crate was spawned at X: §a{x}§7, Y: §a{y}§7, Z: §a{z}§7."
set: "{prefix} §7You have successfully set §a{crate} §7Crate, the crate was spawned at X: §a{x}§7, Y: §a{y}§7, Z: §a{z}§7.{line}§cRestart the Server!"
keyall-giver: "{prefix} §7You gave §aAll Online Player's +{amount}x {crate} §7Crate Keys."
keyall-receiver: "{prefix} §e{giver} §7gave you §a+{amount}x {crate} §7Crate Keys."
break: "{prefix} §7You can't break {color}{crate} §7Crate!"
```
### Contributing
If you find a bug or want to help improve the plugin, join my discord server and make a suggestion.

### License
AquaCrates is released under the Apache license. See the LICENSE file for more information.

### Support
If you need help with AquaCrates, you can contact me on my discord server [AquaDevs](https://discord.gg/VFFzjceP6E) or create an issue on the GitHub repository.
