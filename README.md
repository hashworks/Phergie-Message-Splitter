# PhergieMessageSplitter

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin to split too long messages send by the bot into multiple commands.

## Install

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `~`.

```
composer require hashworks/phergie-message-splitter-plugin
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

```php
new \hashworks\Phergie\Plugin\MessageSplitter\Plugin
```

Optionally you can adjust the tildePrefix variable (defaults to true) when your server isn't prefixing usernames with ~:
```php
new \hashworks\Phergie\Plugin\MessageSplitter\Plugin(array('tildePrefix' => false))
```
Mostly this won't be the case.

## Developers

Since we need the correct username/ident and host set by the server to calculate the maximum message length this plugin updates those two as well.
Inform me if you developed a plugin to keep those up to date!