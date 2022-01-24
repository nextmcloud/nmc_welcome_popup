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
	
	/** @var SlideController|\PHPUnit\Framework\MockObject\MockObject */
	private $controllerMock;

	/** @var IRequest|\PHPUnit\Framework\MockObject\MockObject */
	protected $request;
	
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
		
		$this->request = $this->createMock(IRequest::class);
		$this->config = $this->createMock(IConfig::class);
		$this->slideManager = $this->createMock(SlideManager::class);
		$this->imageManager = $this->createMock(ImageManager::class);
		$this->admin = $this->createMock(Admin::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);

		$this->controller = new SlideController(
			'nmc_welcome_popup',
			$this->request,
			$this->config,
			$this->slideManager,
			$this->imageManager,
			$this->admin,
			$this->l10n,
			$this->createMock(ILogger::class),
			$this->urlGenerator
		);

		$this->controllerMock = $this->getMockBuilder(SlideController::class)
			->setMethods(['unsetImageParam', 'deleteImageValues'])
			->setConstructorArgs([
				'nmc_welcome_popup',
				$this->request,
				$this->config,
				$this->slideManager,
				$this->imageManager,
				$this->admin,
				$this->l10n,
				$this->createMock(ILogger::class),
				$this->urlGenerator
			])
			->getMock();
	}

	public function dataSlides() {
		return [[
			[
				"1" => [
					"en_GB" => [
						"title" => "English title slide 1",
						"primary_button_label" => "English primary button slide 1",
						"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide1/en",
						"secondary_button_desc" => "English secondary button desc slide 1",
						"content" => "English content 1"
					],
					"de_DE" => [
						"title" => "Deutsch title slide 1",
						"primary_button_label" => "Deutsch primary button slide 1",
						"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide1/de",
						"secondary_button_desc" => "Deutsch secondary button desc slide 1",
						"content" => "Deutsch content 1"
					],
					"image_uploaded" => "welcome_image_1"
				],
				"2" => [
					"en_GB" => [
						"title" => "",
						"primary_button_label" => "English primary button slide 2",
						"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide2/en",
						"secondary_button_desc" => "English secondary button desc slide 2",
						"content" => "English content 2"
					],
					"de_DE" => [
						"title" => "Deutsch title slide 2",
						"primary_button_label" => "Deutsch primary button slide 2",
						"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide2/de",
						"secondary_button_desc" => "Deutsch secondary button desc slide 1",
						"content" => "Deutsch content 2"
					],
					"image_uploaded" => "welcome_image_2"
				]
			], 1
		]];
	}

	/**
	 * @dataProvider dataSlides
	 * @param $slideArray
	 * @param $slideId
	 */
	public function testAddSlideWithNoError($slideArray, $slideId) {
		$this->slideManager
			->expects($this->once())
			->method('addSlide')
			->with($slideId, $slideArray[$slideId])
			->willReturn($slideArray[$slideId]);
		// $this->config
		// 	->expects($this->once())
		// 	->method('deleteAppFromAllUsers')
		// 	->with('nmc_welcome_popup');
		$this->l10n
			->expects($this->once())
			->method('t')
			->willReturnCallback(function ($str) {
				return $str;
			});

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

	/**
	 * @dataProvider dataSlides
	 * @param $slideArray
	 * @param $slideId
	 */
	public function testAddSlideWithError($slideArray, $slideId) {
		$checks = [
					'en_GB' => [
						'title'=> 'No Title',
						'primary_button_label' => 'No Primary button label',
						'primary_button_url' => 'No Primary button url',
						'secondary_button_desc' => 'No Secondary button description given',
						'content' => 'No Text given'
					],
					'de_DE' => [
						'title' => 'Kein Titel',
						'primary_button_label' => 'Keine primäre Schaltflächenbeschriftung',
						'primary_button_url' => 'Keine primäre button url',
						'secondary_button_desc' => 'Keine Beschreibung der sekundären Schaltfläche angegeben',
						'content' => 'Kein Text angegeben'
					]
				];

		foreach($checks as $lang => $params) {
			foreach($params as $field => $errorMsg) {
				$tmpVal = $slideArray[$slideId][$lang][$field];
				$slideArray[$slideId][$lang][$field] = "";
				$expected = new DataResponse([
						'data' => [
							'message' => $errorMsg,
						],
						'status' => 'error'
					], Http::STATUS_BAD_REQUEST);
				$response = $this->controller->addSlide($slideId, $slideArray[$slideId]);

				$this->assertEquals($expected, $response);
				$slideArray[$slideId][$lang][$field] = $tmpVal;
			}
		}
		$checks = [
					'en_GB' => [
						'primary_button_url' => 'Not a valid URL for primary button'
					],
					'de_DE' => [
						'primary_button_url' => 'Keine gültige URL für die primäre Schaltfläche'
					]
				];
		foreach($checks as $lang => $params) {
			foreach($params as $field => $errorMsg) {
				$tmpVal = $slideArray[$slideId][$lang][$field];
				$slideArray[$slideId][$lang][$field] = "htt://localhost/index.php/settings/admin/";
				$expected = new DataResponse([
						'data' => [
							'message' => $errorMsg,
						],
						'status' => 'error'
					], Http::STATUS_BAD_REQUEST);
				$response = $this->controller->addSlide($slideId, $slideArray[$slideId]);

				$this->assertEquals($expected, $response);
				$slideArray[$slideId][$lang][$field] = $tmpVal;
			}
		}
	}

	/**
	 * @dataProvider dataSlides
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

	/**
	 * @dataProvider dataSlides
	 * @param $slideArray
	 * @param $slideId
	*/
	public function testRemoveSlide($slideArray, $slideId) {
		$this->slideManager
			->expects($this->once())
			->method('removeSlide')
			->with($slideId)
			->willReturn($slideArray[$slideId]);
		$this->controllerMock->expects($this->any())
			->method('deleteImageValues')
			->with($this->equalTo($slideArray[$slideId]['image_uploaded']));
		$this->controllerMock->deleteImageValues($slideArray[$slideId]['image_uploaded']);
		$this->imageManager->expects($this->any())
			->method('delete')
			->with($this->equalTo($slideArray[$slideId]['image_uploaded']));
		$this->l10n
			->expects($this->once())
			->method('t')
			->willReturnCallback(function ($str) {
				return $str;
			});
		$expected = new DataResponse(
			[
				'data' =>
					[
						'message' => 'Deleted',
					],
				'status' => 'success',
			]
		);

		$this->assertEquals($expected, $this->controller->removeSlide($slideId));
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

	public function testUploadImageNoData() {
		$this->request
			->expects($this->once())
			->method('getParam')
			->with('key')
			->willReturn('welcome_image_1');
		$this->request
			->expects($this->once())
			->method('getUploadedFile')
			->with('image')
			->willReturn(null);
		$this->l10n
			->expects($this->any())
			->method('t')
			->willReturnCallback(function ($str) {
				return $str;
			});

		$expected = new DataResponse(
			[
				'data' =>
					[
						'message' => 'No file uploaded',
					],
				'status' => 'failure',
			],
			Http::STATUS_UNPROCESSABLE_ENTITY
		);

		$this->assertEquals($expected, $this->controller->uploadImage());
	}

	public function testUploadImageInvalidMimeType() {
		$this->request
			->expects($this->once())
			->method('getParam')
			->with('key')
			->willReturn('welcome_image_1');
		$this->request
			->expects($this->once())
			->method('getUploadedFile')
			->with('image')
			->willReturn([
				'tmp_name' => __DIR__  . '/../../../../../tests/data/lorem.txt',
				'type' => 'application/pdf',
				'name' => 'welcome_image.pdf',
				'error' => 0,
			]);
		$this->l10n
			->expects($this->any())
			->method('t')
			->willReturnCallback(function ($str) {
				return $str;
			});

		$this->imageManager->expects($this->once())
			->method('updateImage')
			->willThrowException(new \Exception('Unsupported image type'));

		$expected = new DataResponse(
			[
				'data' =>
					[
						'message' => 'Unsupported image type',
					],
				'status' => 'failure'
			],
			Http::STATUS_UNPROCESSABLE_ENTITY
		);

		$this->assertEquals($expected, $this->controller->uploadImage());
	}

	public function dataUpdateImages() {
		return [
			['image/jpeg'],
			['image/jpeg'],
			['image/gif'],
			['image/png'],
			['image/svg+xml'],
			['image/svg']
		];
	}

	/** @dataProvider dataUpdateImages
	 * @param $mimeType
	 */
	public function testUpdateImageNormalImageUpload($mimeType) {
		$tmpImage = \OC::$server->getTempManager()->getTemporaryFolder() . '/welcome_image.svg';
		$destination = \OC::$server->getTempManager()->getTemporaryFolder();

		touch($tmpImage);
		copy(__DIR__ . '/../../../../../tests/data/testimage.png', $tmpImage);
		$this->request
			->expects($this->once())
			->method('getParam')
			->with('key')
			->willReturn('welcome_image_1');
		$this->request
			->expects($this->once())
			->method('getUploadedFile')
			->with('image')
			->willReturn([
				'tmp_name' => $tmpImage,
				'type' => $mimeType,
				'name' => 'welcome_image.svg',
				'error' => 0,
			]);
		$this->l10n
			->expects($this->any())
			->method('t')
			->willReturnCallback(function ($str) {
				return $str;
			});

		$this->config
			->expects($this->once())
			->method('setAppValue')
			->with('nmc_welcome_popup', 'welcome_image_1_mime', $mimeType);

		$this->imageManager->expects($this->once())
			->method('getImageUrl')
			->with('welcome_image_1')
			->willReturn('imageUrl');

		$this->imageManager->expects($this->once())
			->method('updateImage')
			->willReturn($mimeType);

		$expected = new DataResponse(
			[
				'data' =>
					[
						'name' => 'welcome_image.svg',
						'imgMime' => $mimeType,
						'url' => 'imageUrl',
						'image' => 'welcome_image_1',
						'message' => 'Saved',
					],
				'status' => 'success'
			]
		);

		$this->assertEquals($expected, $this->controller->uploadImage());
	}

	public function dataPhpUploadErrors() {
		return [
			[UPLOAD_ERR_INI_SIZE, 'The uploaded file exceeds the upload_max_filesize directive in php.ini'],
			[UPLOAD_ERR_FORM_SIZE, 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'],
			[UPLOAD_ERR_PARTIAL, 'The file was only partially uploaded'],
			[UPLOAD_ERR_NO_FILE, 'No file was uploaded'],
			[UPLOAD_ERR_NO_TMP_DIR, 'Missing a temporary folder'],
			[UPLOAD_ERR_CANT_WRITE, 'Could not write file to disk'],
			[UPLOAD_ERR_EXTENSION, 'A PHP extension stopped the file upload'],
		];
	}

	/**
	 * @dataProvider dataPhpUploadErrors
	 * @param $error
	 * @param $expectedErrorMessage
	 */
	public function testUpdateImageUploadWithInvalidImageUpload($error, $expectedErrorMessage) {
		$this->request
			->expects($this->once())
			->method('getParam')
			->with('key')
			->willReturn('welcome_image_1');
		$this->request
			->expects($this->once())
			->method('getUploadedFile')
			->with('image')
			->willReturn([
				'tmp_name' => '',
				'type' => 'text/svg',
				'name' => 'welcome_image.svg',
				'error' => $error,
			]);
		$this->l10n
			->expects($this->any())
			->method('t')
			->willReturnCallback(function ($str) {
				return $str;
			});

		$expected = new DataResponse(
			[
				'data' =>
					[
						'message' => $expectedErrorMessage
					],
				'status' => 'failure'
			],
			Http::STATUS_UNPROCESSABLE_ENTITY
		);
		$this->assertEquals($expected, $this->controller->uploadImage());
	}

	/**
	 * @dataProvider dataSlides
	 * @param $slideArray
	 * @param $slideId
	 */
	public function testUnsetImageParam($slideArray, $slideId) {
		$slide = $slideArray[$slideId];
		$slide['image_uploaded'] = "";
		$this->slideManager->expects($this->once())
			->method('getSlidesToDisplay')
			->with($slideId)
			->willReturn($slideArray[$slideId]);
		$this->slideManager
			->expects($this->once())
			->method('addSlide')
			->with($this->equalTo($slideId), $this->equalTo($slide))
			->willReturn(true);

		$this->assertTrue($this->controller->unsetImageParam($slideId));
	}

	public function testDeleteImageValues() {
		$this->config->expects($this->exactly(2))
			->method('deleteAppValue')->withConsecutive(['nmc_welcome_popup', 'welcome_image_1_mime'], ['nmc_welcome_popup', 'welcome_image_1_cachebuster'])
			->willReturnOnConsecutiveCalls(true, true);
		$this->controller->deleteImageValues('welcome_image_1');
	}

	public function mockUnsetImageParam($slideId) {
		$this->slideManager->expects($this->once())
			->method('getSlidesToDisplay');
		$this->slideManager
			->expects($this->any())
			->method('addSlide');
		$this->controllerMock->unsetImageParam($slideId);
	}

	public function mockDeleteImageValues($key) {
		$this->config->expects($this->exactly(2))
			->method('deleteAppValue');
		$this->controllerMock->deleteImageValues($key);
	}

	/**
	* @dataProvider dataSlides
	* @param $slideArray
	* @param $slideId
	*/
	public function testDeleteImage($slideArray, $slideId) {
		$this->mockUnsetImageParam($slideId);
		$this->mockDeleteImageValues($slideArray[$slideId]['image_uploaded']);
		$this->imageManager->expects($this->once())
			->method('delete')
			->with($this->equalTo($slideArray[$slideId]['image_uploaded']));
		$this->l10n
			->expects($this->once())
			->method('t')
			->willReturnCallback(function ($str) {
				return $str;
			});
		$expected = new DataResponse(
			[
				'data' =>
					[
						'message' => 'Deleted',
					],
				'status' => 'success',
			]
		);

		$this->assertEquals($expected, $this->controller->deleteImage($slideArray[$slideId]['image_uploaded'], $slideId));
	}

	public function testDeleteImageDoesNotExist() {
		$slideId = 1;
		$key = 'welcome_image_';
		$this->mockUnsetImageParam($slideId);
		$this->mockDeleteImageValues($key);
		$this->imageManager->expects($this->once())
			->method('delete')
			->with($key)
			->willThrowException(new NotFoundException('Not found'));
		$this->l10n
			->expects($this->once())
			->method('t')
			->willReturnCallback(function ($str) {
				return $str;
			});
		$expected = new DataResponse(
			[
				'data' =>
					[
						'message' => 'Not Found',
					],
				'status' => 'not found',
			]
		);
		$this->assertEquals($expected, $this->controller->deleteImage($key, $slideId));
	}

	/**
	* @dataProvider dataSlides
	* @param $slideArray
	* @param $slideId
	*/
	public function testImageNotPermittedToDelete($slideArray, $slideId) {
		$this->mockUnsetImageParam($slideId);
		$this->mockDeleteImageValues($slideArray[$slideId]['image_uploaded']);
		$this->imageManager->expects($this->once())
			->method('delete')
			->with($slideArray[$slideId]['image_uploaded'])
			->willThrowException(new NotPermittedException());
		$expected = new DataResponse(
			[
				'data' => [
					'message' => 0
				],
				'status' => 'failure - not permitted',
			],
			Http::STATUS_CONFLICT
		);
		$this->assertEquals($expected, $this->controller->deleteImage($slideArray[$slideId]['image_uploaded'], $slideId));
	}

	public function testDeleteImageWithNoFileName() {
		$slideId = 1;
		$key = 'welcome_image_1';
		$this->mockUnsetImageParam($slideId);
		$this->mockDeleteImageValues($key);
		$this->imageManager->expects($this->any())
			->method('delete')
			->with('')
			->willThrowException(new \Exception());
		$expected = new DataResponse(
				[
					'data' => [
						'message' => 0
					],
					'status' => 'failure - unknown exception',
				],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		$this->assertEquals($expected, $this->controller->deleteImage($key, $slideId));
	}

}
