<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\NMC_Welcome_Popup\Listener;

use OCA\NMC_Welcome_Popup\Notification\AppHint;
use OCP\App\Events\AppEnableEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/** @template-implements IEventListener<AppEnableEvent> */
class AppEnabledListener implements IEventListener {

	public function __construct(
		private AppHint $appHint,
	) {
	}

	public function handle(Event $event): void {
		if (!$event instanceof AppEnableEvent) {
			return;
		}

		$this->appHint->dismissNotification($event->getAppID());
	}
}
