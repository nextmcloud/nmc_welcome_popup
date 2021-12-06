import Vue from 'vue'
import { generateFilePath } from '@nextcloud/router'
import { translate, translatePlural } from '@nextcloud/l10n'

import App from './App.vue'
// eslint-disable-next-line
__webpack_public_path__ = generateFilePath('nmc_welcome_popup', '', 'js/');

Vue.prototype.t = translate
Vue.prototype.n = translatePlural
// eslint-disable-next-line
Vue.prototype.oc_defaults = window.oc_defaults

const el = document.createElement('div')
el.id = 'nmc_welcome_popup'
document.querySelector('body').appendChild(el)

const View = Vue.extend(App)
const vm = new View().$mount(el)

window.OCA.NMC_Welcome_Popup = {
	open: vm.open,
	previewSlide: vm.previewSlide
}
