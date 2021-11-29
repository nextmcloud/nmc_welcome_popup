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
use OCP\AppFramework\Http;
use OCP\IConfig;
use OCP\IRequest;
use OC\Template\SCSSCacher;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IURLGenerator;
use OCP\AppFramework\Http\FileDisplayResponse;

use OCA\NMC_Welcome_Popup\SlideManager;
use OCA\NMC_Welcome_Popup\ImageManager;

class SlideController extends Controller {

	/** @var IConfig */
	protected $config;

	/** @var string */
	protected $userId;

	/** @var SlideManager */
	protected $slideManager;

	/** @var ImageManager */
	private $imageManager;

	/** @var array|false|string[] */
	protected $slides = [];

	private $scssCacher;
	/** @var IURLGenerator */

	/** @var IL10N */
	private $l10n;

	/** @var ILogger */
	protected $logger;

	/** @var IURLGenerator */
	private $urlGenerator;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param string $userId
	 * @param SCSSCacher $scssCacher
	 * @param ImageManager $imageManager
	 * @param IL10N $l
	 * @param IURLGenerator $urlGenerator
	 */
	public function __construct($appName,
								IRequest $request,
								IConfig $config,
								$userId,
								SlideManager $slideManager,
								SCSSCacher $scssCacher,
								ImageManager $imageManager,
								IL10N $l,
								ILogger $logger,
								IURLGenerator $urlGenerator) {
		parent::__construct($appName, $request);

		$this->config = $config;
		$this->userId = $userId;
		$this->slideManager = $slideManager;
		$this->imageManager = $imageManager;
		$this->scssCacher = $scssCacher;
		$this->l10n = $l;
		$this->logger = $logger;
		$this->urlGenerator = $urlGenerator;
	}

	public function addSlide($slide) {
		//$this->logger->debug('Slide: ' . print_r($slide, true));
		foreach ($slide as $section => $field) {
			if (is_array($field)) {
				$slide[$section] = array_map('trim', $field);
			} else {
				$slide[$section] = trim($field);
			}
		}
		$error = null;
		$en = 'en_GB';
		$du = 'de_DE';
		if ($slide[$en]['title'] == '') {
			$error = 'No Title';
		} elseif ($slide[$en]['primary_button_label'] == '') {
			$error = 'No Primary button label';
		} elseif ($slide[$en]['primary_button_url'] == '') {
			$error = 'No Primary button url';
		} elseif ($slide[$en]['secondary_button_desc'] == '') {
			$error = 'No Secondary button description given';
		} elseif ($slide[$en]['display_probability'] == '') {
			$error = 'No Disaply probabilty given';
		} elseif ($slide[$en]['content'] == '') {
			$error = 'No Text given';
		}
		
		if ($slide[$du]['title'] == '') {
			$error = 'Kein Titel';
		} elseif ($slide[$du]['primary_button_label'] == '') {
			$error = 'Keine primäre Schaltflächenbeschriftung';
		} elseif ($slide[$du]['primary_button_url'] == '') {
			$error = 'Keine primäre button url';
		} elseif ($slide[$du]['secondary_button_desc'] == '') {
			$error = 'Keine Beschreibung der sekundären Schaltfläche angegeben';
		} elseif ($slide[$du]['display_probability'] == '') {
			$error = 'Keine Disaply-Wahrscheinlichkeit angegeben';
		} elseif ($slide[$du]['content'] == '') {
			$error = 'Kein Text angegeben';
		}
		
		if ($error !== null) {
			return new DataResponse([
				'data' => [
					'message' => $error,
				],
				'status' => 'error'
			], Http::STATUS_BAD_REQUEST);
		}

		$this->slideManager->addSlide($slide);

		// reprocess server scss for preview
		$cssCached = $this->scssCacher->process(\OC::$SERVERROOT, 'core/css/css-variables.scss', 'core');

		return new DataResponse (
			[
				'data' =>
					[
						'message' => $this->l10n->t('Saved'),
						'serverCssUrl' => $this->urlGenerator->linkTo('', $this->scssCacher->getCachedSCSS('core', '/core/css/css-variables.scss'))
					],
				'status' => 'success'
			]
		);
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param string $key
	 * @param bool $useSvg
	 * @return FileDisplayResponse|NotFoundResponse
	 * @throws NotPermittedException
	 */
	public function getImage(string $key, bool $useSvg = true) {
		try {
			$file = $this->imageManager->getImage($key, $useSvg);
		} catch (NotFoundException $e) {
			return new NotFoundResponse();
		}

		$response = new FileDisplayResponse($file);
		$csp = new Http\ContentSecurityPolicy();
		$csp->allowInlineStyle();
		$response->setContentSecurityPolicy($csp);
		$response->cacheFor(3600);
		$response->addHeader('Content-Type', $this->config->getAppValue($this->appName, $key . 'Mime', ''));
		$response->addHeader('Content-Disposition', 'attachment; filename="' . $key . '"');
		if (!$useSvg) {
			$response->addHeader('Content-Type', 'image/png');
		} else {
			$response->addHeader('Content-Type', $this->config->getAppValue($this->appName, $key . 'Mime', ''));
		}
		return $response;
	}

	/**
	 * @return DataResponse
	 * @throws NotPermittedException
	 */
	public function uploadImage(): DataResponse {
		$key = $this->request->getParam('key');
		$image = $this->request->getUploadedFile('image');
		$error = null;
		$phpFileUploadErrors = [
			UPLOAD_ERR_OK => $this->l10n->t('The file was uploaded'),
			UPLOAD_ERR_INI_SIZE => $this->l10n->t('The uploaded file exceeds the upload_max_filesize directive in php.ini'),
			UPLOAD_ERR_FORM_SIZE => $this->l10n->t('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'),
			UPLOAD_ERR_PARTIAL => $this->l10n->t('The file was only partially uploaded'),
			UPLOAD_ERR_NO_FILE => $this->l10n->t('No file was uploaded'),
			UPLOAD_ERR_NO_TMP_DIR => $this->l10n->t('Missing a temporary folder'),
			UPLOAD_ERR_CANT_WRITE => $this->l10n->t('Could not write file to disk'),
			UPLOAD_ERR_EXTENSION => $this->l10n->t('A PHP extension stopped the file upload'),
		];
		if (empty($image)) {
			$error = $this->l10n->t('No file uploaded');
		}
		if (!empty($image) && array_key_exists('error', $image) && $image['error'] !== UPLOAD_ERR_OK) {
			$error = $phpFileUploadErrors[$image['error']];
		}

		if ($error !== null) {
			return new DataResponse(
				[
					'data' => [
						'message' => $error
					],
					'status' => 'failure',
				],
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		try {
			$mime = $this->imageManager->updateImage($key, $image['tmp_name']);
			$this->config->setAppValue($this->appName, $key . 'Mime', $mime);
		} catch (\Exception $e) {
			return new DataResponse(
				[
					'data' => [
						'message' => $e->getMessage()
					],
					'status' => 'failure',
				],
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		$name = $image['name'];
		$cssCached = $this->scssCacher->process(\OC::$SERVERROOT, 'core/css/css-variables.scss', 'core');

		return new DataResponse(
			[
				'data' =>
					[
						'name' => $name,
						'imgMime' => $mime,
						'url' => $this->imageManager->getImageUrl($key),
						'image' => $key,
						'message' => $this->l10n->t('Saved'),
						'serverCssUrl' => $this->urlGenerator->linkTo('', $this->scssCacher->getCachedSCSS('core', '/core/css/css-variables.scss'))
					],
				'status' => 'success'
			]
		);
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param string $key
	 * @return DataResponse
	 */
	public function deleteImage(string $key) {
		try {
			$this->imageManager->delete($key);
			$this->config->deleteAppValue($this->appName, $key . 'Mime');
			$slide = $this->slideManager->getSlidesToDisplay();
			if (is_array($slide) && !empty($slide)) {
				$slide['image_uploaded'] = "";
				$this->slideManager->addSlide($slide);
			}
		} catch (NotFoundException $e) {
			return new DataResponse(
				[
					'data' => [
						'message' => $e->getMessage()
					],
					'status' => 'failure',
				],
				Http::STATUS_NOT_FOUND
			);
		} catch (NotPermittedException $e) {
			return new DataResponse(
				[
					'data' => [
						'message' => $e->getMessage()
					],
					'status' => 'failure',
				],
				Http::STATUS_CONFLICT
			);
		}

		return new DataResponse(
			[
				'data' =>
					[
						'message' => $this->l10n->t('Deleted'),
					],
				'status' => 'success'
			]
		);
	}
}
