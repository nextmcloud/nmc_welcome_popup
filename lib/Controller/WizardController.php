<?php
/**
 * @copyright Copyright (c) 2016 Joas Schilling <coding@schilljs.com>
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

namespace OCA\NMC_Welcome_Popup\Controller;


use OCA\NMC_Welcome_Popup\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Defaults;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\L10N\IFactory;
use OCA\NMC_Welcome_Popup\ImageManager;

class WizardController extends Controller {

	/** @var IConfig */
	protected $config;

	/** @var string */
	protected $userId;

	/** @var Defaults */
	protected $theming;

	/** @var IGroupManager */
	protected $groupManager;

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
	 * @param Defaults $theming
	 */
	public function __construct($appName,
								IRequest $request,
								IConfig $config,
								$userId,
								Defaults $theming,
								ImageManager $imageManager,
								IGroupManager $groupManager,
								IFactory $l10nFactory) {
		parent::__construct($appName, $request);

		$this->config = $config;
		$this->userId = $userId;
		$this->theming = $theming;
		$this->groupManager = $groupManager;
		$this->l10nFactory = $l10nFactory;
		$this->imageManager = $imageManager;

		$this->slides = explode(',', $this->config->getAppValue(Application::APP_ID, 'slides', 'video,values,apps,clients,final'));
	}

	/**
	 * @NoAdminRequired
	 * @return DataResponse
	 */
	public function disable() {
		$this->config->setUserValue($this->userId, 'nmc_welcome_popup', 'show', 0);
		return new DataResponse();
	}

	/**
	 * @NoAdminRequired
	 * @return JsonResponse
	 */
	public function show() {
		// $appStore = $this->config->getSystemValue('appstoreenabled', true);

		// $data = [
		// 	'desktop'      => $this->config->getSystemValue('customclient_desktop', $this->theming->getSyncClientUrl()),
		// 	'android'      => $this->config->getSystemValue('customclient_android', $this->theming->getAndroidClientUrl()),
		// 	'ios'          => $this->config->getSystemValue('customclient_ios', $this->theming->getiOSClientUrl()),
		// 	'appStore'     => $appStore,
		// 	'useTLS'       => $this->request->getServerProtocol() === 'https',
		// 	'macOSProfile' => \OCP\Util::linkToRemote('dav') . 'provisioning/apple-provisioning.mobileconfig',
		// ];

		// echo $this->l10nFactory->findLanguage();exit;
		$language = $this->l10nFactory->findLanguage();
		$imageURL = $this->imageManager->getImageUrl('welcome_image');

		$slides = [];
		$slideArray = json_decode($this->config->getAppValue('nmc_welcome_popup', 'welcome_slides', '0'), true);
		foreach($slideArray as $slide){
			$slides[] = array_merge($slide["$language"],array('image_url' => $imageURL));
		}

		return new JSONResponse([
			// 'hasVideo' => in_array('video', $this->slides, true),
			'slides' => array_values(array_filter($slides, function ($slide) {
				return $slide !== null;
			}))
		]);
	}

	// public function staticSlide($name, $params) {
	// 	if (!in_array(substr($name, 5), $this->slides, true)) {
	// 		return null;
	// 	}

	// 	$template = new \OCP\Template($this->appName, $name, false);

	// 	foreach($params as $key => $value){
	// 		$template->assign($key, $value);
	// 	}

	// 	return [
	// 		'type' => 'inline',
	// 		'content' => $template->fetchPage($params)
	// 	];
	// }
}
