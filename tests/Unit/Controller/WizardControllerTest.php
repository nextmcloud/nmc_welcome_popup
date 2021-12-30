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


use OCA\NMC_Welcome_Popup\Controller\WizardController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Defaults;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\L10N\IFactory;
use OCA\NMC_Welcome_Popup\ImageManager;
use Test\TestCase;

/**
 * Class WizardControllerTest
 *
 * @package OCA\NMC_Welcome_Popup\Tests\Controller
 * @group DB
 */
class WizardControllerTest extends TestCase {
	/** @var IConfig|\PHPUnit\Framework\MockObject\MockObject */
	protected $config;

	/** @var IGroupManager|\PHPUnit\Framework\MockObject\MockObject */
	protected $groupManager;

	private $l10nFactory;

	/** @var ImageManager|\PHPUnit\Framework\MockObject\MockObject */
	private $imageManager;

	protected function setUp(): void {
		parent::setUp();
		$this->config = $this->createMock(IConfig::class);
		$this->groupManager = $this->createMock(IGroupManager::class);
		$this->l10nFactory = $this->createMock(IFactory::class);
		$this->imageManager = $this->createMock(ImageManager::class);
	}

	/**
	 * @param string $user
	 * @return WizardController
	 */
	protected function getController($user = 'test') {
		return new WizardController(
			'nmc_welcome_popup',
			$this->createMock(IRequest::class),
			$this->config,
			$user,
			\OC::$server->query(Defaults::class),
			$this->imageManager,
			$this->groupManager,
			$this->l10nFactory
		);
	}

	public function dataDisable() {
		return [
			['test1'],
			['test2'],
		];
	}

	/**
	 * @dataProvider dataDisable
	 * @param string $user
	 */
	public function testDisable($user) {
		$controller = $this->getController($user);

		$this->config->expects($this->once())
			->method('setUserValue')
			->with($user, 'nmc_welcome_popup', 'show', 0);

		$response = $controller->disable();

		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
	}

	public function dataGetSlides() {
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
			"3" => [
				"en_GB" => [
					"title" => "English title slide 3",
					"primary_button_label" => "English primary button slide 3",
					"primary_button_url" => "http => //localhost/index.php/settings/admin/nmc_welcome_popup/slide3/en",
					"secondary_button_desc" => "English secondary button desc slide 3",
					"content" => "English content 3"
				],
				"de_DE" => [
					"title" => "Deutsch title slide 3",
					"primary_button_label" => "Deutsch primary button slide 3",
					"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide3/de",
					"secondary_button_desc" => "Deutsch secondary button desc slide 3",
					"content" => "Deutsch content 3"
				],
				"image_uploaded" => "welcome_image_3"
			]
		]
		]];
	}

	/**
	 * @dataProvider dataGetSlides
	 * @param $slideArray
	 */
	public function testShowUser($slideArray) {
		$this->config->expects($this->exactly(2))
			->method('getAppValue')
			->withConsecutive(['nmc_welcome_popup', 'slides', 'video,values,apps,clients,final'], ['nmc_welcome_popup', 'welcome_slides', '0'])
			->willReturnOnConsecutiveCalls('video,values,apps,clients,final', json_encode($slideArray));
		$this->l10nFactory->expects($this->once())
			->method('findLanguage')
			->willReturn('de_DE');
		$this->imageManager->expects($this->any())
			->method('getImageUrl')
			->willReturnOnConsecutiveCalls(
				'/my/image/welcome_image_1?v=0',
				'/my/image/welcome_image_3?v=0',
			);
		foreach($slideArray as $id => $slide){
			$slides[] = array_merge($slide["de_DE"],array('image_url' => "/my/image/welcome_image_" . $id . "?v=0"));
		}
		$controller = $this->getController();

		$expected = new JSONResponse([
			'slides' => array_values(array_filter($slides, function ($slide) {
				return $slide !== null;
			}))
		]);
		$response = $controller->show();

		$this->assertEquals($expected, $response);
	}

}
