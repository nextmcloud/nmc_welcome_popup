<?php
/**
 * @copyright Copyright (c) 2016 Lukas Reschke <lukas@statuscode.ch>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Jan-Christoph Borchardt <hey@jancborchardt.net>
 * @author Julius Härtl <jus@bitgrid.net>
 * @author Lukas Reschke <lukas@statuscode.ch>
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

namespace OCA\NMC_Welcome_Popup\Tests\Settings;

use OCA\NMC_Welcome_Popup\SlideManager;
use OCA\NMC_Welcome_Popup\ImageManager;
use OCA\NMC_Welcome_Popup\Settings\Admin;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\ILogger;
use Test\TestCase;

class AdminTest extends TestCase {
	/** @var Admin|\PHPUnit\Framework\MockObject\MockObject */
	private $admin;
	/** @var IConfig|\PHPUnit\Framework\MockObject\MockObject */
	private $config;
	/** @var IURLGenerator|\PHPUnit\Framework\MockObject\MockObject */
	private $urlGenerator;
	/** @var ImageManager|\PHPUnit\Framework\MockObject\MockObject */
	private $imageManager;
	/** @var SlideManager|\PHPUnit\Framework\MockObject\MockObject */
	private $slideManager;
	/** @var IL10N|\PHPUnit\Framework\MockObject\MockObject */
	private $l10n;

	protected function setUp(): void {
		parent::setUp();
		$this->config = $this->createMock(IConfig::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->slideManager = $this->createMock(SlideManager::class);
		$this->imageManager = $this->createMock(ImageManager::class);

		$this->admin = new Admin(
			$this->config,
			$this->l10n,
			$this->createMock(ILogger::class),
			$this->slideManager,
			$this->imageManager,
			$this->urlGenerator,
		);
	}

	public function dataGetSlides() {
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
			"5" => [
				"en_GB" => [
					"title" => "English title slide 5",
					"primary_button_label" => "English primary button slide 5",
					"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide2/en",
					"secondary_button_desc" => "English secondary button desc slide 5",
					"content" => "vmd,b,db,dd,b,dbmd,b"
				],
				"de_DE" => [
					"title" => "Deutsch title slide 5","primary_button_label" => "Deutsch primary button slide 5","primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide2/de","secondary_button_desc" => "Deutsch secondary button desc slide 5","content" => "v,sblvdz,bl dzlb ldz bl dz b"
				],
				"image_uploaded" => ""
			],
			"3" => [
				"en_GB" => [
					"title" => "English title slide 3",
					"primary_button_label" => "English primary button slide 3",
					"primary_button_url" => "http => //localhost/index.php/settings/admin/nmc_welcome_popup/slide2/en",
					"secondary_button_desc" => "English secondary button desc slide 3",
					"content" => "mcs,vsvs v sv slvls vlks vl sklv klsv"
				],
				"de_DE" => [
					"title" => "Deutsch title slide 3",
					"primary_button_label" => "Deutsch primary button slide 3",
					"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide2/de",
					"secondary_button_desc" => "Deutsch secondary button desc slide 3",
					"content" => "a,vksmnkvs vksvjlks vls vlsv"
				],
				"image_uploaded" => "welcome_image_3"
			],
			"4" => [
				"en_GB" => [
					"title" => "English title slide 4",
					"primary_button_label" => "English primary button slide 4",
					"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide2/en",
					"secondary_button_desc" => "English secondary button desc slide 4",
					"content" => "cma,c,ac a c,a ca c"
				],
				"de_DE" => [
					"title" => "Deutsch title slide 4",
					"primary_button_label" => "Deutsch primary button slide 4",
					"primary_button_url" => "http://localhost/index.php/settings/admin/nmc_welcome_popup/slide2/de",
					"secondary_button_desc" => "Deutsch secondary button desc slide 4",
					"content" => "cms,csmvsv s v"
				],
				"image_uploaded" => ""
			]
		]
		]];
	}

	/**
	 * @dataProvider dataGetSlides
	 * @param $slides
	 */
	public function testGetFormWithSlides($slides) {
		$this->config
			->expects($this->once())
			->method('getAppValue')
			->with('nmc_welcome_popup', 'slideIds', '1')
			->willReturn('1,5,3,4,2');
		$this->config
			->expects($this->once())
			->method('setAppValue')
			->with('nmc_welcome_popup', 'slideIds', '1,5,3,4');
		$this->slideManager
			->expects($this->once())
			->method('getSlides')
			->willReturn($slides);
		$this->imageManager
			->expects($this->once())
			->method('getImageUrl')
			->with('welcome_image_1')
			->willReturn('/my/image/welcome_image_1?v=0');
		$this->urlGenerator
			->expects($this->once())
			->method('linkToRoute')
			->with('nmc_welcome_popup.Slide.uploadImage')
			->willReturn('/my/route');
		$parameters = [];
		$parameters = $slides[1];
		$parameters['image_url'] = '/my/image/welcome_image_1?v=0';
		$parameters['slide_ids'] = [1,5,3,4];
		$parameters['uploadImageRoute'] = '/my/route';
		$parameters['errorMessage'] = '';

		$expected = new TemplateResponse('nmc_welcome_popup', 'settings-admin', $parameters, '');
		$this->assertEquals($expected, $this->admin->getForm());
	}

	public function testGetFormWithNoSlides() {
		$this->config
			->expects($this->once())
			->method('getAppValue')
			->with('nmc_welcome_popup', 'slideIds', '1')
			->willReturn('1');
		$this->config
			->expects($this->once())
			->method('setAppValue')
			->with('nmc_welcome_popup', 'slideIds', '1');
		$this->slideManager
			->expects($this->once())
			->method('getSlides')
			->willReturn([]);
		$this->imageManager
			->expects($this->any())
			->method('getImageUrl')
			->with('welcome_image_1')
			->willReturn('');
		$this->urlGenerator
			->expects($this->once())
			->method('linkToRoute')
			->with('nmc_welcome_popup.Slide.uploadImage')
			->willReturn('/my/route');
		$parameters = [];
		$parameters['image_url'] = '';
		$parameters['slide_ids'] = [1];
		$parameters['uploadImageRoute'] = '/my/route';
		$parameters['errorMessage'] = '';

		$expected = new TemplateResponse('nmc_welcome_popup', 'settings-admin', $parameters, '');
		$this->assertEquals($expected, $this->admin->getForm());
	}

	public function testGetSection() {
		$this->assertSame('nmc_welcome_popup', $this->admin->getSection());
	}

	public function testGetPriority() {
		$this->assertSame(5, $this->admin->getPriority());
	}
}
