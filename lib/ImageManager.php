<?php
/**
 * @copyright Copyright (c) 2016 Julius Härtl <jus@bitgrid.net>
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

namespace OCA\NMC_Welcome_Popup;

use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Files\SimpleFS\ISimpleFile;
use OCP\IConfig;
use OCP\IURLGenerator;
use Psr\Log\LoggerInterface;

class ImageManager {

	/** @var IConfig */
	private $config;
	/** @var IAppData */
	private $appData;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var array */
	private $supportedImageKeys = ['background', 'logo', 'logoheader', 'favicon', 'welcome_image'];
	/** @var LoggerInterface */
	private $logger;

	public function __construct(
		IConfig $config,
		IAppData $appData,
		IURLGenerator $urlGenerator,
		LoggerInterface $logger
	) {
		$this->config = $config;
		$this->appData = $appData;
		$this->urlGenerator = $urlGenerator;
		$this->logger = $logger;
	}

	public function getImageUrl(string $key): string {
		$cacheBusterCounter = $this->config->getAppValue('nmc_welcome_popup', $key . '_cachebuster', '0');
		return $this->urlGenerator->linkToRoute(
			'nmc_welcome_popup.Slide.getImage',
			['key' => $key]
		) . '?v=' . $cacheBusterCounter;
	}

	public function getImageUrlAbsolute(string $key): string {
		return $this->urlGenerator->getAbsoluteURL($this->getImageUrl($key));
	}

	/**
	 * @param string $key
	 * @return ISimpleFile
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 */
	public function getImage(string $key): ISimpleFile {
		$folder = $this->appData->getFolder('images');

		if (!$folder->fileExists($key)) {
			throw new NotFoundException();
		}

		return $folder->getFile($key);
	}

	public function getCustomImages(): array {
		$images = [];
		foreach ($this->supportedImageKeys as $key) {
			$images[$key] = [
				'mime' => $this->config->getAppValue('theming', $key . '_mime', ''),
				'url' => $this->getImageUrl($key),
			];
		}
		return $images;
	}

	public function delete(string $key): void {
		try {
			$file = $this->appData->getFolder('images')->getFile($key);
			$file->delete();
		} catch (NotFoundException $e) {
			throw $e;
		} catch (NotPermittedException $e) {
			throw $e;
		}
	}

	public function updateImage(string $key, string $tmpFile) {
		try {
			$this->delete($key);
		} catch (NotFoundException $e) {
			// ignorieren, wenn noch nichts existiert
		} catch (NotPermittedException $e) {
			throw new \Exception("Can't upload image. Permission denied.");
		}

		try {
			$folder = $this->appData->getFolder('images');
		} catch (NotFoundException $e) {
			$folder = $this->appData->newFolder('images');
		}

		$target = $folder->newFile($key);
		$supportedFormats = $this->getSupportedUploadImageFormats();
		$detectedMimeType = mime_content_type($tmpFile);

		if (!in_array($detectedMimeType, $supportedFormats, true)) {
			throw new \Exception('Unsupported image type');
		}

		$target->putContent(file_get_contents($tmpFile));

		// 🔴 vorher nur cachebuster gesetzt
		$this->config->setAppValue('nmc_welcome_popup', $key . '_mime', $detectedMimeType);
		$this->config->setAppValue('nmc_welcome_popup', $key . '_cachebuster', substr(time(), -4));

		return $detectedMimeType;
	}


	/**
	 * Returns a list of supported mime types for image uploads.
	 *
	 * @return array
	 */
	private function getSupportedUploadImageFormats(): array {
		return ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/svg'];
	}
}
