<?php
/**
 * @copyright Copyright (c) 2016, Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
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

namespace OCA\NMC_Welcome_Popup\Tests\Controller;

use OCA\NMC_Welcome_Popup\Controller\SlideController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IURLGenerator;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Files\SimpleFS\ISimpleFile;

use OCA\NMC_Welcome_Popup\SlideManager;
use OCA\NMC_Welcome_Popup\ImageManager;
use OCA\NMC_Welcome_Popup\Settings\Admin;

use Test\TestCase;

class SlideControllerTest extends TestCase {

	/** @var SlideController */
	private $controller;

	/** @var IConfig|\PHPUnit\Framework\MockObject\MockObject */
	protected $config;

	/** @var SlideManager|\PHPUnit\Framework\MockObject\MockObject */
	protected $slideManager;

	/** @var ImageManager|\PHPUnit\Framework\MockObject\MockObject */
	private $imageManager;

	/** @var Admin|\PHPUnit\Framework\MockObject\MockObject */
	private $admin;

	/** @var IL10N|\PHPUnit\Framework\MockObject\MockObject */
	private $l10n;

	/** @var IURLGenerator|\PHPUnit\Framework\MockObject\MockObject */
	private $urlGenerator;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);
		$this->slideManager = $this->createMock(SlideManager::class);
		$this->imageManager = $this->createMock(ImageManager::class);
		$this->admin = $this->createMock(Admin::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);

		$this->controller = new SlideController(
			'nmc_welcome_popup',
			$this->createMock(IRequest::class),
			$this->config,
			$this->slideManager,
			$this->imageManager,
			$this->admin,
			$this->l10n,
			$this->createMock(ILogger::class),
			$this->urlGenerator
		);
	}

	public function dataAddSlideWithNoError() {
		return [[
			[
				"1" => [
					"en_GB" => [
						"title" => "English title slide 1",
						"primary_button_label" => "English primary button slide 1",
						"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide2/en",
						"secondary_button_desc" => "English secondary button desc slide 1",
						"content" => "vms,v s,vms,vms,vms,vmsv"
					],
					"de_DE" => [
						"title" => "Deutsch title slide 1",
						"primary_button_label" => "Deutsch primary button slide 1",
						"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide2/de",
						"secondary_button_desc" => "Deutsch secondary button desc slide 1",
						"content" => "cakcnkanvkavnkav"
					],
					"image_uploaded" => "welcome_image_1"
				],
			], 1
		]];
	}

	/**
	 * @dataProvider dataAddSlideWithNoError
	 * @param $slideArray
	 * @param $slideId
	 */
	public function testAddSlideWithNoError($slideArray, $slideId) {
		$this->slideManager
			->expects($this->once())
			->method('addSlide')
			->with($slideId, $slideArray[$slideId])
			->willReturn($slideArray[$slideId]);
		$this->config
			->expects($this->once())
			->method('deleteAppFromAllUsers')
			->with('nmc_welcome_popup');
		$this->l10n
			->expects($this->once())
			->method('t')
			->with('Saved')
			->willReturn('Saved');

		$expected = new DataResponse (
			[
				'data' =>
					[
						'message' => 'Saved',
					],
				'status' => 'success'
			]
		);
		$response = $this->controller->addSlide($slideId, $slideArray[$slideId]);

		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertEquals($expected, $response);
	}

	public function dataAddSlideWithError() {
		return [[
			[
				"2" => [
					"en_GB" => [
						"title" => "",
						"primary_button_label" => "English primary button slide 2",
						"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide2/en",
						"secondary_button_desc" => "English secondary button desc slide 2",
						"content" => "vms,v s,vms,vms,vms,vmsv"
					],
					"de_DE" => [
						"title" => "Deutsch title slide 2",
						"primary_button_label" => "Deutsch primary button slide 2",
						"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide2/de",
						"secondary_button_desc" => "Deutsch secondary button desc slide 1",
						"content" => "cakcnkanvkavnkav"
					],
					"image_uploaded" => "welcome_image_2"
				],
			], 2
		]];
	}

	/**
	 * @dataProvider dataAddSlideWithError
	 * @param $slideArray
	 * @param $slideId
	 */
	public function testAddSlideWithError($slideArray, $slideId) {
		// $this->slideManager
		// 	->expects($this->any())
		// 	->method('addSlide')
		// 	->with($slideId, $slideArray[$slideId])
		// 	->willReturn($slideArray[$slideId]);
		// $this->config
		// 	->expects($this->any())
		// 	->method('deleteAppFromAllUsers')
		// 	->with('nmc_welcome_popup');
		// $this->l10n
		// 	->expects($this->any())
		// 	->method('t')
		// 	->with('Saved')
		// 	->willReturn('Saved');

		$expected = new DataResponse([
				'data' => [
					'message' => 'No Title',
				],
				'status' => 'error'
			], Http::STATUS_BAD_REQUEST);
		$response = $this->controller->addSlide($slideId, $slideArray[$slideId]);

		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
		$this->assertEquals($expected, $response);
	}

	/**
	 * @dataProvider dataAddSlideWithNoError
	 * @param $slideArray
	 * @param $slideId
	 */
	public function testGetSlide($slideArray, $slideId) {
		$this->slideManager->expects($this->once())
			->method('getSlidesToDisplay')
			->with($slideId)
			->willReturn($slideArray[$slideId]);
		$this->imageManager->expects($this->any())
			->method('getImageUrl')
			->willReturn('/my/image/welcome_image_' . $slideId . '?v=0');
		$params = $slideArray[$slideId];
		$params['image_url'] = '/my/image/welcome_image_' . $slideId . '?v=0';
		$expected = $this->controller->getSlide($slideId);
		$this->assertEquals($expected, $params);
	}

	public function testGetImageNotExistent() {
		$this->imageManager->method('getImage')
			->with($this->equalTo('welcome_image_1'))
			->willThrowException(new NotFoundException());

		$expected = new Http\NotFoundResponse();
		$this->assertEquals($expected, $this->controller->getImage('welcome_image_1'));
	}

	public function testGetImage() {
		$file = $this->createMock(ISimpleFile::class);
		$this->imageManager->expects($this->once())
			->method('getImage')
			->willReturn($file);
		$this->config
			->expects($this->any())
			->method('getAppValue')
			->with('nmc_welcome_popup', 'welcome_image_1_mime', '')
			->willReturn('image/svg+xml');

		@$expected = new Http\FileDisplayResponse($file);
		$csp = new Http\ContentSecurityPolicy();
		$csp->allowInlineStyle();
		$expected->setContentSecurityPolicy($csp);
		$expected->cacheFor(3600);
		$expected->addHeader('Content-Type', 'image/svg+xml');
		$expected->addHeader('Content-Disposition', 'attachment; filename="welcome_image_1"');
		@$this->assertEquals($expected, $this->controller->getImage('welcome_image_1'));
	}


}
