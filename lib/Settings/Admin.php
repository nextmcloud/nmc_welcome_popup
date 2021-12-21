<?php
/**
 * @copyright Copyright (c) 2016 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bjoern Schiessle <bjoern@schiessle.org>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Julius Härtl <jus@bitgrid.net>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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

namespace OCA\NMC_Welcome_Popup\Settings;

use OCA\NMC_Welcome_Popup\SlideManager;
use OCA\NMC_Welcome_Popup\ImageManager;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\ISettings;
use OCP\ILogger;

class Admin implements ISettings {

	/** @var IConfig */
	private $config;

	/** @var IL10N */
	private $l;

	/** @var SlideManager */
	protected $slideManager;

	/** @var ImageManager */
	private $imageManager;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var ILogger */
	protected $logger;

	public function __construct(IConfig $config,
								IL10N $l,
								ILogger $logger,
								SlideManager $slideManager,
								ImageManager $imageManager,
								IURLGenerator $urlGenerator) {
		$this->config = $config;
		$this->l = $l;
		$this->slideManager = $slideManager;
		$this->imageManager = $imageManager;
		$this->urlGenerator = $urlGenerator;
		$this->logger = $logger;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$errorMessage = '';
		$parameters = [];
		$popUpId = 1;
		$slideIds = explode(',', $this->config->getAppValue('nmc_welcome_popup', 'slideIds', '1'));
		$popUps = $this->slideManager->getPopUps();
		if (!is_array($popUps) || empty($popUps[$popUpId]['slides'])) {
			$slides = [];
		} else {
			$slides = $popUps[$popUpId]['slides'];
		}
		if (empty($slides)) {
			$slideIds = [1];
		} else {
			$slideIds = array_keys($slides);
		}
		$this->config->setAppValue('nmc_welcome_popup', 'slideIds', implode(',', $slideIds));
		$parameters['image_url'] = '';
		if (isset($slides[$slideIds[0]])) {
			$parameters = $slides[$slideIds[0]];
			if (!empty($parameters['image_uploaded'])) {
				$imageURL = $this->imageManager->getImageUrl($parameters['image_uploaded']);
				$parameters['image_url'] = $imageURL;
			}
		}
		$parameters['popup_id'] = $popUpId;
		$parameters['quota'] = $popUps[$popUpId]['quota'];
		$this->logger->info('Quota: ' . $parameters['quota']);
		$parameters['slide_ids'] = $slideIds;
		$parameters['uploadImageRoute'] = $this->urlGenerator->linkToRoute('nmc_welcome_popup.Slide.uploadImage');
		$parameters['errorMessage'] = $errorMessage;

		return new TemplateResponse('nmc_welcome_popup', 'settings-admin', $parameters, '');
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection(): string {
		return 'nmc_welcome_popup';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 * the admin section. The forms are arranged in ascending order of the
	 * priority values. It is required to return a value between 0 and 100.
	 *
	 * E.g.: 70
	 */
	public function getPriority(): int {
		return 5;
	}
}
