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

return [
	'routes' => [
		['name' => 'Wizard#show', 'url' => '/wizard', 'verb' => 'GET'],
		['name' => 'Wizard#disable', 'url' => '/wizard', 'verb' => 'DELETE'],
		['name' => 'Slide#addSlide', 'url' => '/ajax/slideSettings/{slideId}', 'verb' => 'POST', 'defaults' => ['slideId' => '1']],
		['name' => 'Slide#getImage', 'url' => '/image/{key}', 'verb' => 'GET'],
		['name' => 'Slide#uploadImage', 'url' => '/ajax/uploadImage', 'verb' => 'POST'],
		['name' => 'Slide#deleteImage', 'url' => '/image/{key}', 'verb' => 'DELETE'],
		['name' => 'Slide#getSlide', 'url' => '/ajax/slideSettings/{slideId}', 'verb' => 'GET', 'defaults' => ['slideId' => '1']],
		['name' => 'Slide#removeSlide', 'url' => '/ajax/slideSettings/{slideId}', 'verb' => 'DELETE', 'defaults' => ['slideId' => '1']],
	],
];
