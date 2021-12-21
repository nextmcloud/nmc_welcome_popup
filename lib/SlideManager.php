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
	 * @param int $popUpId
	 * @param int $slideId
	 * @return array[]
	 */
	public function getSlidesToDisplay($popUpId, $slideId) {
		$popUp = $this->getPopUp($popUpId);
		$slides = $popUp['slides'];
		$quota = $popUp['quota'];

		$langSlides = [];
		if (isset($slides[$slideId])) {
			$langSlides = $slides[$slideId];
			$langSlides['quota'] = $quota;
		}

		return $langSlides;
	}

	/**
	 * @param int $popUpId
	 * @return array[]
	 */
	public function getSlides($popUpId) {
		$jsonEncodedList = $this->config->getAppValue('nmc_welcome_popup', 'welcome_slides', '');
		$popUps = json_decode($jsonEncodedList, true);
		if (!is_array($popUps) || empty($popUps[$popUpId]['slides'])) {
			return [];
		}

		return $popUps[$popUpId]['slides'];
	}

	/**
	 * @return array[]
	 */
	public function getPopUps() {
		$jsonEncodedList = $this->config->getAppValue('nmc_welcome_popup', 'welcome_slides', '');
		$popUps = json_decode($jsonEncodedList, true);
		if (!is_array($popUps) || empty($popUps)) {
			return [];
		}

		return $popUps;
	}

	/**
	 * @param int $popUpId
	 * @return array[]
	 */
	public function getPopUp($popUpId) {
		$jsonEncodedList = $this->config->getAppValue('nmc_welcome_popup', 'welcome_slides', '');
		$popUps = json_decode($jsonEncodedList, true);
		if (!is_array($popUps) || empty($popUps[$popUpId])) {
			return [];
		}

		return $popUps[$popUpId];
	}

	/**
	 * @param int $popUpId
	 * @param int $slideId
	 * @param array $slide
	 * @param array $quota
	 * @return array
	 */
	public function addSlide($popUpId, $slideId, $slide, $quota) {
		$popUps = $this->getPopUps();
		$popUps[$popUpId]['slides'][$slideId] = $slide;
		$popUps[$popUpId]['quota'] = $quota;
		$this->config->setAppValue('nmc_welcome_popup', 'welcome_slides', json_encode($popUps));

		return $popUps[$popUpId]['slides'][$slideId];
	}

	/**
	 * @param int $popUpId
	 * @param int $slideId
	 * @return array
	 */
	public function removeSlide($popUpId, $slideId) {
		$popUps = $this->getPopUps();
		if (isset($popUps[$popUpId]['slides'][$slideId])) {
			$slide = $popUps[$popUpId]['slides'][$slideId];
			unset($popUps[$popUpId]['slides'][$slideId]);
		} else {
			$slide = [];
		}
		if (empty($popUps[$popUpId]['slides'])) {
			unset($popUps[$popUpId]);
		}
		if (!empty($popUps)) {
			$this->config->setAppValue('nmc_welcome_popup', 'welcome_slides', json_encode($popUps));
		} else {
			$this->config->setAppValue('nmc_welcome_popup', 'welcome_slides', '');
		}
		return $slide;
	}

}
