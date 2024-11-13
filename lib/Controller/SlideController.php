<?php
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\NMC_Welcome_Popup\Controller;

use OCA\NMC_Welcome_Popup\SlideManager;
use OCA\NMC_Welcome_Popup\ImageManager;
use OCA\NMC_Welcome_Popup\Settings\Admin;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IL10N;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;

class SlideController extends Controller {

	/** @var IConfig */
	protected $config;

	/** @var SlideManager */
	protected $slideManager;

	/** @var ImageManager */
	private $imageManager;

	/** @var Admin */
	private $admin;

	/** @var array|false|string[] */
	protected $slides = [];

	/** @var IL10N */
	private $l10n;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param SlideManager $slideManager
	 * @param ImageManager $imageManager
	 * @param Admin $admin
	 * @param IL10N $l
	 */
	public function __construct($appName,
								IRequest $request,
								IConfig $config,
								SlideManager $slideManager,
								ImageManager $imageManager,
								Admin $admin,
								IL10N $l) {
		parent::__construct($appName, $request);

		$this->config = $config;
		$this->slideManager = $slideManager;
		$this->imageManager = $imageManager;
		$this->admin = $admin;
		$this->l10n = $l;
	}

	public function addSlide($slideId, $slide) {
		foreach ($slide as $section => $field) {
			if (is_array($field)) {
				$slide[$section] = array_map('trim', $field);
			} else {
				$slide[$section] = trim($field);
			}
		}
		$error = null;
		$en = 'en_GB';
		$de = 'de_DE';
		$primaryBtnUrl = $slide[$en]['primary_button_url'];
		if ($slide[$en]['title'] == '') {
			$error = 'No Title';
		} elseif ($slide[$en]['primary_button_label'] == '') {
			$error = 'No Primary button label';
		} elseif ($primaryBtnUrl == '') {
			$error = 'No Primary button URL';
		} elseif (filter_var($primaryBtnUrl, FILTER_VALIDATE_URL) === false || (strpos($primaryBtnUrl, 'http://') !== 0 && strpos($primaryBtnUrl, 'https://') !== 0)) {
			$error = 'Not a valid URL for primary button';
		} elseif ($slide[$en]['secondary_button_desc'] == '') {
			$error = 'No Secondary button description given';
		} elseif ($slide[$en]['content'] == '') {
			$error = 'No Text given';
		}

		$primaryBtnUrl = $slide[$de]['primary_button_url'];
		if ($slide[$de]['title'] == '') {
			$error = 'Kein Titel';
		} elseif ($slide[$de]['primary_button_label'] == '') {
			$error = 'Keine primäre Schaltflächenbeschriftung';
		} elseif ($primaryBtnUrl == '') {
			$error = 'Keine primäre button url';
		} elseif (filter_var($primaryBtnUrl, FILTER_VALIDATE_URL) === false || (strpos($primaryBtnUrl, 'http://') !== 0 && strpos($primaryBtnUrl, 'https://') !== 0)) {
			$error = 'Keine gültige URL für die primäre Schaltfläche';
		} elseif ($slide[$de]['secondary_button_desc'] == '') {
			$error = 'Keine Beschreibung der sekundären Schaltfläche angegeben';
		} elseif ($slide[$de]['content'] == '') {
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

		$this->slideManager->addSlide($slideId, $slide);
		// $this->config->deleteAppFromAllUsers($this->appName);

		return new DataResponse (
			[
				'data' =>
					[
						'message' => $this->l10n->t('Saved'),
					],
				'status' => 'success'
			]
		);
	}

	/**
	 * @param $slideId
	 * @return array
	 */
	public function getSlide($slideId) {
		$params = $this->slideManager->getSlidesToDisplay($slideId);
		if (!empty($params['image_uploaded'])) {
			$imageURL = $this->imageManager->getImageUrl('welcome_image_' . $slideId);
			$params['image_url'] = $imageURL;
		}
		return $params;
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
	public function getImage(string $key) {
		try {
			$file = $this->imageManager->getImage($key);
		} catch (NotFoundException $e) {
			return new NotFoundResponse();
		}

		$response = new FileDisplayResponse($file);
		$csp = new Http\ContentSecurityPolicy();
		$csp->allowInlineStyle();
		$response->setContentSecurityPolicy($csp);
		$response->cacheFor(3600);
		// $response->addHeader('Content-Type', $this->config->getAppValue($this->appName, $key . '_mime', ''));
		$response->addHeader('Content-Disposition', 'attachment; filename="' . $key . '"');
		$response->addHeader('Content-Type', $this->config->getAppValue($this->appName, $key . '_mime', ''));
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
			$this->config->setAppValue($this->appName, $key . '_mime', $mime);
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

		return new DataResponse(
			[
				'data' =>
					[
						'name' => $name,
						'imgMime' => $mime,
						'url' => $this->imageManager->getImageUrl($key),
						'image' => $key,
						'message' => $this->l10n->t('Saved'),
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
	 * @param int $slideId
	 * @return DataResponse
	 */
	public function deleteImage(string $key, int $slideId) {
		$this->unsetImageParam($slideId);
		$this->deleteImageValues($key);
		try {
			$this->imageManager->delete($key);
		} catch (NotFoundException $e) {
			return new DataResponse(
				[
					'data' => [
						'message' => $this->l10n->t('Not Found'),
					],
					'status' => 'not found',
				]
			);
		} catch (NotPermittedException $e) {
			return new DataResponse(
				[
					'data' => [
						'message' => $e->getCode()
					],
					'status' => 'failure - not permitted',
				],
				Http::STATUS_CONFLICT
			);
		} catch (\InvalidArgumentException) {
			return new DataResponse(
				[
					'data' => [
						'message' => $e->getCode()
					],
					'status' => 'failure - invalid argument exception',
				],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		} catch (\Exception $e) {
			return new DataResponse(
				[
					'data' => [
						'message' => $e->getCode()
					],
					'status' => 'failure - unknown exception',
				],
				Http::STATUS_INTERNAL_SERVER_ERROR
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

	/**
	 *
	 * @param int $slideId
	 * @return array[]
	 */
	public function unsetImageParam(int $slideId) {
		$slide = $this->slideManager->getSlidesToDisplay($slideId);
		if (is_array($slide) && !empty($slide)) {
			$slide['image_uploaded'] = "";
			$slide = $this->slideManager->addSlide($slideId, $slide);
		}
		return $slide;
	}

	public function deleteImageValues(string $key) {
		$this->config->deleteAppValue($this->appName, $key . '_mime');
		$this->config->deleteAppValue($this->appName, $key . '_cachebuster');
	}

	public function removeSlide(int $slideId) {
		$slide = $this->slideManager->removeSlide($slideId);
		$key = (isset($slide['image_uploaded'])) ? $slide['image_uploaded'] : null;
		if (!empty($key)) {
			$this->deleteImageValues($key);
			try {
				$this->imageManager->delete($key);
			} catch (NotFoundException $e) {
			} catch (NotPermittedException $e) {
			} catch (\InvalidArgumentException $e) {
			} catch (\Exception $e) {
			}
		}
		return new DataResponse (
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
