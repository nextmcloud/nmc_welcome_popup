<template>
	<NcModal
		v-if="showModal && slideList.length > 0"
		id="nmc_welcome_popup"
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
	.modal-content {
		padding: 1.5rem;

		.modal-body {

			img {
				width: 100%;
			}

			.content {
				margin: 0.5rem 0;
			}
		}

		.modal-footer {
			box-sizing: border-box;
			display: flex;
			justify-content: flex-end;
			right: 0;
			bottom: 0;
			padding: 0;
			padding-top: 10px;
			position: relative;
			text-align: right;
			width: 100%;

			button {
				border: 0;
				border-radius: var(--telekom-radius-standard);
				box-sizing: border-box;
				font-size: var(--default-font-size);
				height: 44px;
				margin: 0.25rem 0.5rem 0.25rem 0;
				min-height: 36px;
				min-width: 44px;
				opacity: 1;
				overflow: hidden;
				padding: 0.5rem 1rem;
				text-overflow: ellipsis;
				white-space: nowrap;
				width: auto;

				&.primary {
					background-color: var(--telekom-color-primary-standard);
   					color: var(--telekom-color-text-and-icon-white-standard) !important;

					&:hover {
						background-color: var(--telekom-color-primary-hovered);
					}
				}

				&:not(.primary) {
					background-color: var(--telekom-color-ui-base);
					border: 1px solid var(--telekom-color-ui-border-standard);
					color: var(--telekom-color-text-and-icon-standard);

					&:hover {
						background-color: var(--color-background-hover);
						border: 1px solid var(--telekom-color-ui-border-hovered);
					}
				}

				&:last-child {
					margin-right: 0;
				}
			}

			.pagination {
				display: flex;
				flex: auto;

				span {
					display: flex;
					flex-shrink: 0;
					justify-content: center;
					align-items: center;
					padding: 0 1rem;
					height: 44px;
					min-width: 44px;
					width: auto;
					color: var(--telekom-color-ui-extra-strong);
					background: none;
					border: 1px solid var(--telekom-color-ui-faint);
					border-left: none;
					border-right: none;
					position: relative;

					&.left-arrow-button, &.right-arrow-button {
						border-left: 1px solid var(--telekom-color-ui-faint);
						border-right: 1px solid var(--telekom-color-ui-faint);
						border-radius: var(--telekom-radius-standard) 0 0 var(--telekom-radius-standard);
						cursor: pointer;
						padding: 0;

						&::after {
							content: "";
							display: block;
							position: absolute;
							top: 0px;
							left: 0px;
							height: 100%;
							width: 44px;
							background-repeat: no-repeat;
							background-position: center;
							background-size: 20px;
							background-image: var(--original-icon-arrow-previous-dark);
						}

						&:hover {
							border-color: var(--telekom-color-primary-standard);
							color: var(--telekom-color-primary-standard);

							&::after {
								filter: invert(17%) sepia(87%) saturate(5418%) hue-rotate(319deg) brightness(85%) contrast(110%);
							}
						}
					}

					&.right-arrow-button {
						border-radius: 0 var(--telekom-radius-standard) var(--telekom-radius-standard) 0;

						&::after {
							background-image: var(--original-icon-arrow-next-dark);
						}
					}
				}
			}
		}
	}
</style>

<script>
import NcModal from '@nextcloud/vue/dist/Components/NcModal'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import IntroVideo from './components/IntroVideo'

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
			if (this.withIntro) {
				return this.slides
			}
			const slides = this.slides
			return slides.slice(1)
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
		this.slides = [IntroVideo]
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
