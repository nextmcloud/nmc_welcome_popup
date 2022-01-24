<?php
/**
 * @copyright Copyright (c) 2016 Julius Härtl <jus@bitgrid.net>
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Julius Haertl <jus@bitgrid.net>
 * @author Julius Härtl <jus@bitgrid.net>
 * @author Michael Weimann <mail@michael-weimann.eu>
 * @author Morris Jobke <hey@morrisjobke.de>
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

namespace OCA\NMC_Welcome_Popup\Tests;

use OCA\NMC_Welcome_Popup\ImageManager;
use OCP\Files\File;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Files\SimpleFS\ISimpleFile;
use OCP\Files\SimpleFS\ISimpleFolder;
use OCP\IConfig;
use OCP\ILogger;
use OCP\IURLGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class ImageManagerTest extends TestCase {

	/** @var IConfig|\PHPUnit\Framework\MockObject\MockObject */
	protected $config;
	/** @var IAppData|\PHPUnit\Framework\MockObject\MockObject */
	protected $appData;
	/** @var ImageManager */
	protected $imageManager;
	/** @var IURLGenerator|\PHPUnit\Framework\MockObject\MockObject */
	private $urlGenerator;
	/** @var ImageManager|\PHPUnit\Framework\MockObject\MockObject */
	private $imageManagerMock;
	/** @var ISimpleFolder|\PHPUnit\Framework\MockObject\MockObject */
	private $folder;
	/** @var ISimpleFile|\PHPUnit\Framework\MockObject\MockObject */
	private $file;
	/** @var ILogger */
	private $logger;

	protected function setUp(): void {
		parent::setUp();
		$this->config = $this->createMock(IConfig::class);
		$this->appData = $this->createMock(IAppData::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->logger = $this->createMock(ILogger::class);
		$this->imageManager = new ImageManager(
			$this->config,
			$this->appData,
			$this->urlGenerator,
			$this->logger
		);
		$this->imageManagerMock = $this->getMockBuilder(ImageManager::class)
			->setMethods(['delete'])
			->setConstructorArgs([
				$this->config,
				$this->appData,
				$this->urlGenerator,
				$this->logger
			])
			->getMock();
		$this->folder = $this->createMock(ISimpleFolder::class);
		$this->file = $this->createMock(ISimpleFile::class);
	}

	public function mockGetImage($key, $file) {
		$this->config->expects($this->once())
			->method('getAppValue')
			->with('nmc_welcome_popup', $key . '_mime', '')
			->willReturn('image/svg+xml');
		$this->appData->expects($this->once())
			->method('getFolder')
			->with('images')
			->willReturn($this->folder);
		if ($file === null) {
			$this->folder->expects($this->once())
				->method('fileExists')
				->with($key)
				->willThrowException(new NotFoundException());
		} else {
			$this->folder->expects($this->once())
				->method('fileExists')
				->with($key)
				->willReturn(true);
			$this->folder->expects($this->once())
				->method('getFile')
				->with($key)
				->willReturn($file);
		}
	}

	public function dataGetImage() {
		return [
			['welcome_image_1'],
		];
	}

	/**
	 * @dataProvider dataGetImage
	 * @param $key
	 */
	public function testGetImageUrl($key) {
		$this->config->expects($this->once())
			->method('getAppValue')
			->with('nmc_welcome_popup', $key . '_cachebuster', '0')
			->willReturn(0);
		$this->urlGenerator->expects($this->once())
			->method('linkToRoute')
			->willReturn('url-to-image');
		$this->assertEquals('url-to-image?v=0', $this->imageManager->getImageUrl($key));
	}

	/**
	 * @dataProvider dataGetImage
	 * @param $key
	 */
	public function testGetImage($key) {
		$this->mockGetImage($key, $this->file);
		$this->assertEquals($this->file, $this->imageManager->getImage($key));
	}

	/**
	 * @dataProvider dataGetImage
	 * @param $key
	 */
	public function testGetImageUnset($key) {
		$this->expectException(NotFoundException::class);

		$this->config->expects($this->once())
			->method('getAppValue')->with('nmc_welcome_popup', $key . '_mime', '')
			->willReturn('');
		$this->imageManager->getImage($key);
	}

	/**
	 * @dataProvider dataGetImage
	 * @param $key
	 */
	public function testGetImageDoesNotExist($key) {
		$this->expectException(NotFoundException::class);
		$this->mockGetImage($key, null);
		$this->imageManager->getImage($key);
	}

	public function dataDeleteImage() {
		return [
			['welcome_image_1', true, true],
			['welcome_image_2', false, true],
			['welcome_image_2', true, false],
		];
	}

	/**
	 * @dataProvider dataDeleteImage
	 * @param $key
	 * @param $fileExists
	 * @param $allowed
	 */
	public function testDeleteImage($key, $fileExists, $allowed) {
		$this->appData->expects($this->once())
			->method('getFolder')
			->with('images')
			->willReturn($this->folder);
		if ($fileExists) {
			$this->folder->expects($this->once())
				->method('getFile')
				->with($key)
				->willReturn($this->file);
			if ($allowed) {
				$this->file->expects($this->once())
					->method('delete');
			} else {
				$this->expectException(NotPermittedException::class);
				$this->file->expects($this->once())
					->method('delete')
					->willThrowException(new NotPermittedException());
			}
		} else {
			$this->expectException(NotFoundException::class);
			$this->folder->expects($this->once())
				->method('getFile')
				->with($key)
				->willThrowException(new NotFoundException());
		}
		$this->imageManager->delete($key);
	}


	public function dataUpdateImage() {
		return [
			['welcome_image_1', __DIR__ . '/../../../../tests/data/testimage.jpg', false],
			['welcome_image_2', __DIR__ . '/../../../../tests/data/testimagelarge.svg', true],
		];
	}

	/**
	 * @dataProvider dataUpdateImage
	 * @param $$key
	 * @param $tmpFile
	 * @param $folderExists
	 */
	public function testUpdateImage($key, $tmpFile, $folderExists) {
		if ($folderExists) {
			$this->folder->expects($this->once())
				->method('getFile')
				->with($key)
				->willReturn($this->file);
			$this->file->expects($this->once())
				->method('delete');
			$this->imageManagerMock->delete($key);
			$this->appData
				->expects($this->any())
				->method('getFolder')
				->with('images')
				->willReturn($this->folder);
		} else {
			$this->imageManagerMock->delete($key);
			$this->appData
				->expects($this->exactly(2))
				->method('getFolder')
				->with('images')
				->willThrowException(new NotFoundException());
			$this->appData
				->expects($this->any())
				->method('newFolder')
				->with('images')
				->willReturn($this->folder);
		}
		$this->folder->expects($this->once())
			->method('newFile')
			->with($key)
			->willReturn($this->file);
		$this->config
			->expects($this->once())
			->method('setAppValue')
			->with('nmc_welcome_popup', $key . '_cachebuster', $this->anything());

		$this->imageManager->updateImage($key, $tmpFile);
	}
}
