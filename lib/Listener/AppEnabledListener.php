<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2020 Morris Jobke <hey@morrisjobke.de>
 *
 * @author Morris Jobke <hey@morrisjobke.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\NMC_Welcome_Popup\Listener;

use OCA\NMC_Welcome_Popup\Notification\AppHint;
use OCP\App\ManagerEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

class AppEnabledListener implements IEventListener {
	/** @var AppHint */
	private $appHint;

	public function __construct(AppHint $appHint) {
		$this->appHint = $appHint;
	}

	public function handle(Event $event): void {
		if (!$event instanceof ManagerEvent) {
			return;
		}

		$this->appHint->dismissNotification($event->getAppID());
	}
}
