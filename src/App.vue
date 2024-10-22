<template>
	<NcModal
		v-if="showModal && slideList.length > 0"
		id="nmc_welcome_popup"
		class="nmc-welcome-popup"
		:has-previous="hasPrevious"
		:has-next="hasNext"
		:size="isMobile ? 'full' : 'normal'"
		name="modal"
		@previous="previous"
		@next="next"
		@close="close">
		<div class="modal-content">
			<div v-if="currentSlide !== 0 || !withIntro" class="modal-header">
				<div class="nmc_welcome_popup-header">
					<h2 v-html="slideList[currentSlide].title" />
				</div>
			</div>
			<div class="modal-body">
				<slot v-if="slideList.length > 0" name="body">
					<div class="image">
						<img v-bind:src="slideList[currentSlide].image_url" />
					</div>
					<div class="content" :key="currentSlide" v-html="slideList[currentSlide].content" />
				</slot>
			</div>
			<div class="modal-footer">
				<div v-if="slideList.length > 1" class="pagination">
					<span class="left-arrow-button" @click="previous"></span>
					<span class="slide-counter">{{ this.currentSlide + 1 }}{{ t('nmc_welcome_popup', ' of ') }}{{ slideList.length }}</span>
					<span class="right-arrow-button" @click="next"></span>
				</div>
				<div class="footer-actions">
					<button class="primary" @click="close">{{ slideList[currentSlide].secondary_button_desc }}</button>
					<a 
						:href="slideList[currentSlide].primary_button_url"
						:label="slideList[currentSlide].primary_button_label"
						target="_blank">
						<button>{{ slideList[currentSlide].primary_button_label }}</button>
					</a>
				</div>
			</div>
		</div>
	</NcModal>
</template>

<style lang="scss" scoped>

</style>

<script>
import { generateUrl } from '@nextcloud/router'
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import axios from '@nextcloud/axios'

export default {
	name: 'App',
	components: {
		NcModal,
	},
	data() {
		return {
			showModal: false,
			withIntro: true,
			slides: [],
			currentSlide: 0,
			fadeDirection: 'next',
			isMobile: window.outerWidth < 1024,
			slidesLoaded: false,
		}
	},
	computed: {
		slideList() {
			return this.slides
		},
		hasNext() {
			return this.currentSlide < this.slideList.length - 1
		},
		hasPrevious() {
			return this.currentSlide > 0
		},
		isLast() {
			return this.currentSlide === this.slideList.length - 1
		},
		isFirst() {
			return this.currentSlide === 0
		},
		startButtonText() {
			return t('nmc_welcome_popup', 'Start using {cloudName}', { cloudName: window.OC.theme.name })
		},
	},
	async created() {
		window.addEventListener('resize', this.onResize)
	},
	beforeDestroy() {
		window.removeEventListener('resize', this.onResize)
	},
	methods: {
		async loadStaticSlides() {
			if (this.slidesLoaded) {
				return
			}

			try {
				const response = await axios.get(generateUrl('/apps/nmc_welcome_popup/wizard'))
				this.slides = this.slides.slice(0, 1)
				this.slides.push(...response.data.slides)
				this.withIntro = response.data.hasVideo
				// this.slidesLoaded = true
			} catch (e) {
				console.error('Failed to load slides')
			}
		},
		async open(withIntro = true) {
			await this.loadStaticSlides()
			this.withIntro = this.withIntro & withIntro
			this.showModal = true
			this.currentSlide = 0
		},
		previewSlide(slide = []) {
			this.slides = this.slides.slice(0, 1)
			this.slides.push(...slide)
			this.withIntro = false
			this.showModal = true
			this.currentSlide = 0
		},
		close() {
			this.showModal = false
			axios.delete(generateUrl('/apps/nmc_welcome_popup/wizard'))
		},
		next() {
			this.fadeDirection = 'next'
			if (this.isLast) {
				return
			}
			this.currentSlide += 1
		},
		previous() {
			this.fadeDirection = 'previous'
			if (this.isFirst) {
				return
			}
			this.currentSlide -= 1
		},
		onResize(event) {
			// Update mobile mode
			this.isMobile = window.outerWidth < 768
		},
	},
}
</script>
