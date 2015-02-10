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
				$event->getConnection()->setUsername($event->getUsername());
			}
		}
	}

	public function handleMessage (Event $event, Queue $queue) {
		if (isset($event->getParams()[0]) && isset($event->getParams()[1])) {
			$command = $event->getCommand();
			$target = $event->getParams()[0];
			$message = $event->getParams()[1];

			// 512 byte max length, 5 go to ":$hostmask :$message\r\n", so 507 - length of the hostmask "nick!user@host"
			$messageMaxLength = 507 - strlen($event->getConnection()->getNickname() . '!' . $event->getConnection()->getUsername() . '@' .
							$event->getConnection()->getHostname() . ' ' . $command . ' ' . $target);
			if (strlen($message) > $messageMaxLength) {
				// Message too long. Resend the part that got cut. If it's too long this function will be called again.
				$method = 'irc' . $command;
				$queue->$method($target, trim(substr($message, $messageMaxLength)));
			}
		}
	}

}
