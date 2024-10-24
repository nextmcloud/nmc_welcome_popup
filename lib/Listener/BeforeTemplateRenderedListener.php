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
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\BackgroundJob\IJobList;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\IUser;
use OCP\IUserSession;

class BeforeTemplateRenderedListener implements IEventListener {
	/**
	 * @var IUserSession
	 */
	private $userSession;
	/**
	 * @var IConfig
	 */
	private $config;
	/**
	 * @var AppHint
	 */
	private $appHint;
	/**
	 * @var IJobList
	 */
	private $jobList;

	public function __construct(
		IConfig $config,
		IUserSession $userSession,
		IJobList $jobList,
		AppHint $appHint
	) {
		$this->userSession = $userSession;
		$this->config = $config;
		$this->appHint = $appHint;
		$this->jobList = $jobList;
	}

	public function handle(Event $event): void {
		if (!$event instanceof BeforeTemplateRenderedEvent || !$event->isLoggedIn()) {
			return;
		}

		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return;
		}

		$version = $this->config->getAppValue('nmc_welcome_popup', 'version', '');
		$show = $this->config->getUserValue($user->getUID(), 'nmc_welcome_popup', 'show', '1');

		if ($show !== $version) {
			\OC_Util::addScript('nmc_welcome_popup', 'nmc_welcome_popup-activate');
		}

		\OCP\Util::addScript('nmc_welcome_popup', 'nmc_welcome_popup-about');
		\OCP\Util::addStyle('nmc_welcome_popup', 'nmc_welcome_popup-style');
		\OCP\Util::addStyle('nmc_welcome_popup', 'style');
	}
}
