// webpack with standard nextcloud config
const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
	...webpackConfig.entry,
	about: path.join(__dirname, 'src', 'js', 'about.js'),
	activate: path.join(__dirname, 'src', 'js', 'activate.js'),
	'personal-settings': path.join(__dirname, 'src', 'js', 'personal-settings.js'),
	'settings-admin': path.join(__dirname, 'src', 'js', 'settings-admin.js'),
}

// Workaround for https://github.com/nextcloud/webpack-vue-config/pull/432 causing problems with nextcloud-vue-collections
webpackConfig.resolve.alias = {}

module.exports = webpackConfig
