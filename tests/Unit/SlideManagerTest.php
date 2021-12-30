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

use OCA\NMC_Welcome_Popup\SlideManager;
use OCP\IConfig;
use OCP\IURLGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class SlideManagerTest extends TestCase {

	/** @var IConfig|\PHPUnit\Framework\MockObject\MockObject */
	protected $config;
	/** @var SlideManager */
	protected $slideManager;
	/** @var IURLGenerator|\PHPUnit\Framework\MockObject\MockObject */
	private $urlGenerator;

	protected function setUp(): void {
		parent::setUp();
		$this->config = $this->createMock(IConfig::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->slideManager = new SlideManager(
			$this->config,
			$this->urlGenerator
		);
		$this->slideManagerMock = $this->getMockBuilder(SlideManager::class)
			->setMethods(['getSlides'])
			->setConstructorArgs([
				$this->config,
				$this->urlGenerator
			])
			->getMock();
	}

	public function dataGetSlides() {
		return [[
				'{
					"1": {
						"en_GB": {
							"title": "English title slide 1",
							"primary_button_label": "English primary button slide 1",
							"primary_button_url": "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide1/en",
							"secondary_button_desc": "English secondary button desc slide 1",
							"content": "English content 1"
						},
						"de_DE": {
							"title": "Deutsch title slide 1",
							"primary_button_label": "Deutsch primary button slide 1",
							"primary_button_url": "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide1/de",
							"secondary_button_desc": "Deutsch secondary button desc slide 1",
							"content": "Deutsch content 1"
						},
						"image_uploaded": "welcome_image_1"
					},
					"3": {
						"en_GB": {
							"title": "English title slide 3",
							"primary_button_label": "English primary button slide 3",
							"primary_button_url": "http: //localhost/index.php/settings/admin/nmc_welcome_popup/slide3/en",
							"secondary_button_desc": "English secondary button desc slide 3",
							"content": "English content 3"
						},
						"de_DE": {
							"title": "Deutsch title slide 3",
							"primary_button_label": "Deutsch primary button slide 3",
							"primary_button_url": "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide3/de",
							"secondary_button_desc": "Deutsch secondary button desc slide 3",
							"content": "Deutsch content 3"
						},
						"image_uploaded": "welcome_image_3"
					}
				}'
			],
			[
				'{
					"5": {
						"en_GB": {
							"title": "English title slide 5",
							"primary_button_label": "English primary button slide 5",
							"primary_button_url": "http: //localhost/index.php/settings/admin/nmc_welcome_popup/slide5/en",
							"secondary_button_desc": "English secondary button desc slide 5",
							"content": "English content 5"
						},
						"de_DE": {
							"title": "Deutsch title slide 5",
							"primary_button_label": "Deutsch primary button slide 5",
							"primary_button_url": "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide5/de",
							"secondary_button_desc": "Deutsch secondary button desc slide 5",
							"content": "Deutsch content 5"
						},
						"image_uploaded": "welcome_image_5"
					}
				}'
			],
			['']
		];
	}

	public function mockGetSlides($slidesJson) {
		$this->config->expects($this->once())
			->method('getAppValue')
			->with('nmc_welcome_popup', 'welcome_slides', '')
			->willReturn($slidesJson);
		$this->slideManagerMock->getSlides();
		$slides = json_decode($slidesJson, true);
		if (!is_array($slides) || empty($slides)) {
			return [];
		}

		return $slides;
	}

	/**
	 * @dataProvider dataGetSlides
	 * @param $slidesJson
	 */
	public function testGetSlidesToDisplay($slidesJson) {
		$slideId = 1;
		$slides = $this->mockGetSlides($slidesJson);
		$expected = (isset($slides[$slideId])) ? $slides[$slideId] : [];
		$this->assertEquals($expected, $this->slideManager->getSlidesToDisplay($slideId));
	}

	/**
	 * @dataProvider dataGetSlides
	 * @param $slidesJson
	 */
	public function testGetSlides($slidesJson) {
		$slideId = 1;
		$this->config->expects($this->once())
		->method('getAppValue')
		->with('nmc_welcome_popup', 'welcome_slides', '')
		->willReturn($slidesJson);
		$slides = json_decode($slidesJson, true);
		if (!is_array($slides) || empty($slides)) {
			$slides = [];
		}

		$this->assertEquals($slides, $this->slideManager->getSlides());
	}

	/**
	 * @dataProvider dataGetSlides
	 * @param $slidesJson
	 */
	public function testAddSlide($slidesJson) {
		$slideId = 4;
		$slide = [
					"en_GB" => [
						"title" => "English title slide 4",
						"primary_button_label" => "English primary button slide 4",
						"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide4/en",
						"secondary_button_desc" => "English secondary button desc slide 4",
						"content" => "English content 4"
					],
					"de_DE" => [
						"title" => "Deutsch title slide 4",
						"primary_button_label" => "Deutsch primary button slide 4",
						"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide4/de",
						"secondary_button_desc" => "Deutsch secondary button desc slide 4",
						"content" => "Deutsch content 4"
					],
					"image_uploaded" => "welcome_image_4"
				];
		$slides = $this->mockGetSlides($slidesJson);
		$slides[$slideId] = $slide;
		$this->config->expects($this->once())
			->method('setAppValue')
			->with('nmc_welcome_popup', 'welcome_slides', json_encode($slides));
		$this->assertEquals($slides[$slideId], $this->slideManager->addSlide($slideId, $slide));
	}

	/**
	 * @dataProvider dataGetSlides
	 * @param $slidesJson
	 */
	public function testRemoveSlide($slidesJson) {
		$slideId = 3;
		$slides = $this->mockGetSlides($slidesJson);
		if (isset($slides[$slideId])) {
			$slide = $slides[$slideId];
			unset($slides[$slideId]);
		} else {
			$slide = [];
		}
		if (!empty($slides)) {
			$slidesJson = json_encode($slides);
		} else {
			$slidesJson = '';
		}
		$this->config->expects($this->once())
			->method('setAppValue')
			->with('nmc_welcome_popup', 'welcome_slides', $slidesJson);
		$this->assertEquals($slide, $this->slideManager->removeSlide($slideId));
	}

}
