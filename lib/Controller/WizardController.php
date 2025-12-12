<?php
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\NMC_Welcome_Popup\Controller;

use OCA\NMC_Welcome_Popup\AppInfo\Application;
use OCA\NMC_Welcome_Popup\ImageManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\L10N\IFactory;

class WizardController extends Controller {

	/** @var IConfig */
	protected $config;

	/** @var string */
	protected $userId;

	/** @var string[] */
	protected $slides = [];

	/** @var IFactory */
	private $l10nFactory;

	/** @var ImageManager */
	private $imageManager;

	/**
	 * @param string    $appName
	 * @param IRequest  $request
	 * @param IConfig   $config
	 * @param string    $userId
	 * @param ImageManager $imageManager
	 * @param IFactory  $l10nFactory
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IConfig $config,
		$userId,
		ImageManager $imageManager,
		IFactory $l10nFactory
	) {
		parent::__construct($appName, $request);

		$this->config = $config;
		$this->userId = $userId;
		$this->l10nFactory = $l10nFactory;
		$this->imageManager = $imageManager;

		$this->slides = explode(
			',',
			$this->config->getAppValue(Application::APP_ID, 'slides', 'video,values,apps,clients,final')
		);
	}

	/**
	 * @NoAdminRequired
	 */
	public function disable(): DataResponse {
		$version = $this->config->getAppValue('nmc_welcome_popup', 'version', '');
		$this->config->setUserValue($this->userId, 'nmc_welcome_popup', 'show', $version);
		return new DataResponse();
	}

	/**
	 * @NoAdminRequired
	 */
	public function show(): JSONResponse {
		$language = $this->l10nFactory->findLanguage();

		$slides = [];

		// Rohwert aus der Config holen; Standard ist ein leeres JSON-Array
		$rawSlides = $this->config->getAppValue('nmc_welcome_popup', 'welcome_slides', '[]');
		$slideArray = json_decode($rawSlides, true);

		// Sicherstellen, dass wir ein Array haben
		if (!is_array($slideArray)) {
			$slideArray = [];
		}

		foreach ($slideArray as $id => $slide) {
			// Slide muss ein Array sein
			if (!is_array($slide)) {
				continue;
			}

			// Sprache muss vorhanden und ein Array sein
			if (!isset($slide[$language]) || !is_array($slide[$language])) {
				// Optional: Fallback auf 'en' oder erste Sprache
				if (isset($slide['en']) && is_array($slide['en'])) {
					$slideData = $slide['en'];
				} else {
					// irgendein erster Eintrag als Fallback
					$first = reset($slide);
					if (!is_array($first)) {
						continue;
					}
					$slideData = $first;
				}
			} else {
				$slideData = $slide[$language];
			}

			$imageURL = $this->imageManager->getImageUrl('welcome_image_' . $id);
			$slides[] = array_merge($slideData, ['image_url' => $imageURL]);
		}

		return new JSONResponse([
			'slides' => array_values(array_filter($slides, static function ($slide) {
				return $slide !== null;
			})),
		]);
	}
}
