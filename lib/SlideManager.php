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

use OCP\App\IAppManager;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\Files\SimpleFS\ISimpleFile;
use OCP\IConfig;

class SlideManager {

	/** @var IConfig */
	protected $config;

	/** @var IAppManager */
	protected $appManager;

	/** @var IAppData */
	protected $appData;

	/** @var ImageManager */
	private $imageManager;


	public function __construct(IConfig $config,
								IAppManager $appManager,
								ImageManager $imageManager,
								IAppData $appData) {
		$this->config = $config;
		$this->appManager = $appManager;
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
