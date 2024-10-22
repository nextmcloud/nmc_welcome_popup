/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: CC0-1.0
 */

import { createAppConfig } from '@nextcloud/vite-config'
import { join } from 'node:path'

export default createAppConfig({
	main: join(__dirname, 'src', 'main.js'),
	about: join(__dirname, 'src', 'js', 'about.js'),
	activate: join(__dirname, 'src', 'js', 'activate.js'),
	'admin-settings': join(__dirname, 'src', 'js', 'settings-admin.js'),
	'personal-settings': join(__dirname, 'src', 'js', 'settings-personal.js'),
}, {
	extractLicenseInformation: {
		includeSourceMaps: true,
		overwriteLicenses: {
			'@nextcloud/axios': 'GPL-3.0-or-later',
		},
	},
	thirdPartyLicense: false,
	config: {
		build: {
			// Just one CSS files for now
			cssCodeSplit: false,
		},
	},
})