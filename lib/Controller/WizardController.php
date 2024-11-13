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

	/** @var array|false|string[] */
	protected $slides = [];

	private $l10nFactory;

	/** @var ImageManager */
	private $imageManager;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param string $userId
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

		$this->slides = explode(',', $this->config->getAppValue(Application::APP_ID, 'slides', 'video,values,apps,clients,final'));
	}

	/**
	 * @NoAdminRequired
	 * @return DataResponse
	 */
	public function disable() {
		$version = $this->config->getAppValue('nmc_welcome_popup', 'version', '');
		$this->config->setUserValue($this->userId, 'nmc_welcome_popup', 'show', $version);
		return new DataResponse();
	}

	/**
	 * @NoAdminRequired
	 * @return JsonResponse
	 */
	public function show() {
		$language = $this->l10nFactory->findLanguage();

		$slides = [];
		$slideArray = json_decode($this->config->getAppValue('nmc_welcome_popup', 'welcome_slides', '0'), true);
		foreach($slideArray as $id=>$slide){
			$imageURL = $this->imageManager->getImageUrl('welcome_image_'.$id);
			$slides[] = array_merge($slide["$language"],array('image_url' => $imageURL));
		}

		return new JSONResponse([
			'slides' => array_values(array_filter($slides, function ($slide) {
				return $slide !== null;
			}))
		]);
	}
}
