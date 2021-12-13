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

use OCP\IConfig;
use OCP\IURLGenerator;

class SlideManager {

	/** @var IConfig */
	protected $config;

	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(IConfig $config,
								IURLGenerator $urlGenerator) {
		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @return array[]
	 */
	public function getSlidesToDisplay($slideId) {
		$slides = $this->getSlides();

		$langSlides = [];
		if (isset($slides[$slideId])) {
			$langSlides = $slides[$slideId];
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
	 * @param int $slideId
	 * @param array $slide
	 * @return array
	 */
	public function addSlide($slideId, $slide) {
		$slides = $this->getSlides();
		$slides[$slideId] = $slide;
		$this->config->setAppValue('nmc_welcome_popup', 'welcome_slides', json_encode($slides));

		return $slides[$slideId];
	}

	/**
	 * @param int $slideId
	 * @return array
	 */
	public function removeSlide($slideId) {
		$slides = $this->getSlides();
		if (isset($slides[$slideId])) {
			$slide = $slides[$slideId];
			unset($slides[$slideId]);
		} else {
			$slide = [];
		}
		if (empty($slide)) {
			$this->config->setAppValue('nmc_welcome_popup', 'welcome_slides', '');
		} else {
			$this->config->setAppValue('nmc_welcome_popup', 'welcome_slides', json_encode($slides));
		}
		return $slide;
	}

}
