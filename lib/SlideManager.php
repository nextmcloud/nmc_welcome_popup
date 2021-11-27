<?php
/**
 * @copyright Copyright (c) 2017 Joas Schilling <coding@schilljs.com>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\NMC_Welcome_Popup;

// use OCA\External\Exceptions\GroupNotFoundException;
// use OCA\External\Exceptions\IconNotFoundException;
// use OCA\External\Exceptions\InvalidDeviceException;
// use OCA\External\Exceptions\InvalidNameException;
// use OCA\External\Exceptions\InvalidTypeException;
// use OCA\External\Exceptions\InvalidURLException;
// use OCA\External\Exceptions\LanguageNotFoundException;
// use OCA\External\Exceptions\SiteNotFoundException;
use OCP\App\IAppManager;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\Files\SimpleFS\ISimpleFile;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\L10N\IFactory;

class SlideManager {

	const TYPE_LINK = 'link';
	const TYPE_SETTING = 'settings';
	const TYPE_LOGIN = 'guest';
	const TYPE_QUOTA = 'quota';

	const DEVICE_ALL = '';
	const DEVICE_ANDROID = 'android';
	const DEVICE_IOS = 'ios';
	const DEVICE_DESKTOP = 'desktop';
	const DEVICE_BROWSER = 'browser';

	/** @var IRequest */
	protected $request;

	/** @var IConfig */
	protected $config;

	/** @var IFactory */
	protected $languageFactory;

	/** @var IAppManager */
	protected $appManager;

	/** @var IGroupManager */
	protected $groupManager;

	/** @var IUserSession */
	protected $userSession;

	/** @var IAppData */
	protected $appData;

	/** @var ImageManager */
	private $imageManager;


	public function __construct(IRequest $request,
								IConfig $config,
								IAppManager $appManager,
								IGroupManager $groupManager,
								IUserSession $userSession,
								IFactory $languageFactory,
								ImageManager $imageManager,
								IAppData $appData) {
		$this->request = $request;
		$this->config = $config;
		$this->appManager = $appManager;
		$this->groupManager = $groupManager;
		$this->userSession = $userSession;
		$this->languageFactory = $languageFactory;
		$this->imageManager = $imageManager;
		$this->appData = $appData;
	}

	/**
	 * @return array[]
	 */
	public function getSlidesToDisplay() {
		$slides = $this->getSlides();

		$langSlides = [];
		foreach ($slides as $id => $langSlide) {
			$langSlides = $langSlide;
		}

		return $langSlides;
	}

	/**
	 * @return array[]
	 */
	public function getSlides() {
		$jsonEncodedList = $this->config->getAppValue('nmc_welcome_popup', 'welcome_slides', '');
		$slides = json_decode($jsonEncodedList, true);
		if (!is_array($slides) || empty($slides)) {
			return [];
		}

		return $slides;
	}

	/**
	 * @param array $slide
	 * @return array
	 * @throws InvalidIDException
	 */
	public function addSlide($slide) {
		$slideNo = 1;

		$slides[$slideNo] = $slide;
		$this->config->setAppValue('nmc_welcome_popup', 'welcome_slides', json_encode($slides));

		return $slides[$slideNo];
	}

}
