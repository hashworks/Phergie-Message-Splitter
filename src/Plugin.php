<?php

namespace hashworks\Phergie\Plugin\MessageSplitter;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Event\UserEvent as Event;

/**
 * Plugin class.
 *
 * @category Phergie
 * @package hashworks\Phergie\Plugin\MessageSplitter
 */
class Plugin extends AbstractPlugin {
	private $tildePrefix = true;

	/**
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		if (isset($config['tildePrefix'])) {
			$this->tildePrefix = boolval($config['tildePrefix']);
		}
	}

	public function getSubscribedEvents () {
		return array(
				'irc.received.mode'   => 'handleMode',
				'irc.sending.notice'  => 'handleMessage',
				'irc.sending.privmsg' => 'handleMessage',
		);
	}

	public function handleMode(Event $event) {
		// Every time the server sets our host we receive a MODE command from "ourself"
		if (isset($event->getTargets()[0])) {
			if ($event->getTargets()[0] == $event->getConnection()->getNickname() &&
					$event->getSource() == $event->getConnection()->getNickname() &&
					$event->getNick()   == $event->getConnection()->getNickname()) {
				// It is. Update the host & user.
				$event->getConnection()->setHostname($event->getHost());
				$username = $event->getUsername();
				if ($this->tildePrefix) {
					$username = substr($username, 1);
				}
				$event->getConnection()->setUsername($username);
			}
		}
	}

	public function handleMessage (Event $event, Queue $queue) {
		if (isset($event->getParams()[0]) && isset($event->getParams()[1])) {
			$command = $event->getCommand();
			$target = $event->getParams()[0];
			$message = $event->getParams()[1];

			// 512 byte max length, 5 go to ":$hostmask :$message\r\n", so 507 - length of the hostmask "nick!user@host"
			$string = $event->getConnection()->getNickname() . '!';
			if ($this->tildePrefix) $string .= '~';
			$string .= $event->getConnection()->getUsername() . '@' . $event->getConnection()->getHostname() . ' ' . $command . ' ' . $target;
			$messageMaxLength = 507 - strlen($string);
			if (strlen($message) > $messageMaxLength) {
				// Message too long. Resend the part that got cut. If it's too long this function will be called again.
				$method = 'irc' . $command;
				$queue->$method($target, trim(substr($message, $messageMaxLength)));
			}
		}
	}

}
