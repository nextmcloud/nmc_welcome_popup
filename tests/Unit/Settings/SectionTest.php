<?php
/**
 * @copyright Copyright (c) 2016 Lukas Reschke <lukas@statuscode.ch>
 *
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

use OCA\NMC_Welcome_Popup\Settings\Section;
use OCP\IL10N;
use OCP\IURLGenerator;
use Test\TestCase;

class SectionTest extends TestCase {
	/** @var IURLGenerator|\PHPUnit\Framework\MockObject\MockObject */
	private $url;
	/** @var IL10N|\PHPUnit\Framework\MockObject\MockObject */
	private $l;
	/** @var Section */
	private $section;

	protected function setUp(): void {
		parent::setUp();
		$this->url = $this->createMock(IURLGenerator::class);
		$this->l = $this->createMock(IL10N::class);

		$this->section = new Section(
			$this->url,
			$this->l
		);
	}

	public function testGetID() {
		$this->assertSame('nmc_welcome_popup', $this->section->getID());
	}

	public function testGetName() {
		$this->l
			->expects($this->once())
			->method('t')
			->with('Welcome Pop-up')
			->willReturn('Welcome Pop-up');

		$this->assertSame('Welcome Pop-up', $this->section->getName());
	}

	public function testGetPriority() {
		$this->assertSame(30, $this->section->getPriority());
	}

	public function testGetIcon() {
		$this->url->expects($this->once())
			->method('imagePath')
			->with('nmc_welcome_popup', 'content-news.svg')
			->willReturn('icon');

		$this->assertSame('icon', $this->section->getIcon());
	}
}
