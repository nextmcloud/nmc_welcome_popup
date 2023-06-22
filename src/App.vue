<template>
	<Modal
		v-if="showModal && slideList.length > 0"
		id="nmc_welcome_popup"
		:has-previous="hasPrevious"
		:has-next="hasNext"
		:size="isMobile ? 'full' : 'normal'"
		name="modal"
		@previous="previous"
		@next="next"
		@close="close">
		<div v-if="currentSlide !== 0 || !withIntro" class="modal-header">
			<div class="nmc_welcome_popup-header">
				<h2 v-html="slideList[currentSlide].title" />
				<button
					class="primary modal-default-button"
					@click="close"></button>
			</div>
		</div>
		<div class="modal-body">
			<slot v-if="slideList.length > 0" name="body">
				<div class="logo">
					<img v-bind:src="slideList[currentSlide].image_url" />
				</div>
				<!-- v-bind:style="{backgroundImage:`url(${slideList[currentSlide].image_url})`, backgroundRepeat: `no-repeat`, backgroundPosition: `center`}" /> -->
				<!-- <transition :name="fadeDirection" mode="out-in"> -->
				<div class="content" :key="currentSlide" v-html="slideList[currentSlide].content" />
				<!-- <div :is="slideList[currentSlide]" v-else @finished="currentSlide++" /> -->
				<!-- </transition> -->
			</slot>
		</div>
		<div class="modal-footer">
			<div class="pagination">
				<span class="Left-arrowBtn" @click="previous">&lt;</span>
				<span class="slide-counter">{{ this.currentSlide + 1 }}{{ t('nmc_welcome_popup', ' of ') }}{{ slideList.length }}</span>
				<span class="Right-arrowBtn" @click="next">&gt;</span>
			</div>
			<div class="footer-actions">
				<button
					class="primary modal-default-button"
					@click="close">{{ slideList[currentSlide].secondary_button_desc }}</button>
				<a :href="slideList[currentSlide].primary_button_url"
					:label="slideList[currentSlide].primary_button_label"
					target="_blank"
					class="button">{{ slideList[currentSlide].primary_button_label }}</a>
			</div>
		</div>
	</Modal>
</template>
<style lang="scss">
	/* Page styling needs to be unscoped, since we load it separately from the server */
	.modal-mask {
		background-color: rgba(0, 0, 0, 0.7);

		&::v-deep .modal-wrapper {
			position: relative;
		}

		&::v-deep .modal-container {
			display: flex;
			flex-direction: column;
			height: 95%;
			width: 95%;
			max-width: 900px;
			max-height: 650px;
			position: relative;
		}
	}

	#nmc_welcome_popup {
		.modal-wrapper {
			.modal-header {
				overflow: hidden;
				flex-shrink: 0;
				height: unset;
				max-height: 150px;
				.nmc_welcome_popup-header {
					background-color: var(--white-color);
					background-image: unset;
					padding: 1.5rem;
					h2 {
						float: left;
						color: var(--scl-color-grey-90);
						font-weight: bold;
						padding: 0;
						margin: 0;
						text-align: left;
						line-height: 1.23;
					}
					button.primary.modal-default-button {
						background: var(--icon-dialog-close);
						background-repeat: no-repeat;
						border: unset;
						width: 24px;
						height: 24px;
						padding: unset;
						float: right;
						margin: unset;
					}
				}
			}
			
			.modal-body {
				padding: 0 1.5rem;
				flex-grow: 1;
				overflow-x: hidden;
				overflow-y: auto;
				& > div {
					flex-grow: 1;
					align-items: center;
					justify-content: center;
				}
				.logo {
					background-size: cover;
					width: unset;
					height: unset;
					max-height: unset;
					margin: unset;
					img {
						width: 100%;
						height: auto;
						display: block;
						margin-left: auto;
						margin-right: auto;
						padding-top: 0.25rem;
					}
				}
				.content {
					padding: 1.25rem 0rem;
					color:var(--scl-color-grey-90);
				}
			}
			.modal-footer {
				display: flex;
				width: 100%;
				margin: 2rem 1.5rem 1.5rem 0rem;
				overflow: unset;
				position: unset;
				justify-content: space-between;
				text-align: right;
				button {
					margin: 10px;
				}
				.pagination {
					margin-left: 1.5rem;
					float: left;
					width: 200px;
					height: 40px;
					opacity: 0.6;
					span {
						float: left;
						padding: 0.5rem;
					}
					.slide-counter {
						border-top: 1px solid #666666;
						border-bottom: 1px solid #666666;
					}
					.Left-arrowBtn {
						border: 1px solid #666666;
						border-top-left-radius: 8px;
						border-bottom-left-radius: 8px;
						cursor: pointer;
						&:hover {
							border-color: var(--color-background-plain);
							color: var(--color-background-plain);
						}
					}
					.Right-arrowBtn {
						&:hover {
							border-color: var(--color-background-plain);
							color: var(--color-background-plain);
						}
						border: 1px solid #666666;
						border-top-right-radius: 8px;
						border-bottom-right-radius: 8px;
						cursor: pointer;
					}
				}
				.footer-actions {
					button {
						margin: 0;
					}
					margin-right: 1.5rem;
					overflow: unset;
					position: unset;
					justify-content: space-between;
					text-align: right;
					button.primary {
						border-radius: 8px;
						font-size: 1rem;
						font-weight: normal;
						padding: 0.375rem 1.5rem 0.5rem 1.5rem;
						height: 40px;
						background-color: unset;
						border-color: var(--scl-color-grey-90);
						margin: 0 0.5rem 0 0;
						color: var(--scl-color-grey-90);
						width: auto;
						max-width: 180px;
						text-overflow: ellipsis;
						white-space: nowrap;
						overflow: hidden;
						vertical-align: middle;
						line-height: 1.23;
						&:hover {
							border-color: var(--color-background-plain);
							color: var(--color-background-plain);
							background-color: var(--white-color);
						}
					}
				}
				a.button {
					background-color: var(--color-background-plain);
					border-color: var(--color-background-plain);
					color: var(--white-color);
					border-radius: 8px;
					padding: 0.625rem 1.5rem;
					height: 40px;
					font-size: 1rem;
					font-weight: normal;
					width: auto;
					max-width: 180px;
					text-overflow: ellipsis;
					white-space: nowrap;
					overflow: hidden;
					vertical-align: middle;
					display: inline-block;
					line-height: 1.23;
					&:hover {
						background-color: var(--scl-color-light-magenta);
					}
				}
			}
		}

		.modal-default-button {
			align-self: flex-end;
		}
	
		.header-close {
			display: none;
		}

		.icon-next {
			display: none;
		}

		.icon-previous {
			display: none;
		}

		a.button {
			-webkit-appearance: button;
			-moz-appearance: button;
			appearance: button;

			text-decoration: none;
			color: initial;
		}

		.page {
			display: flex;
			flex-direction: row;
			flex-wrap: wrap;
			margin: auto;

			h3 {
				margin: 10px 0 10px;
				line-height: 120%;
				padding: 0;
			}
			.image {
				padding: 20px;
				max-width: calc(50% - 40px);
				flex-grow: 1;
				img {
					width: 100%;
				}
			}
			.content {
				padding: 20px;
				width: 100%;
			}
			p {
				margin-bottom: 20px;
			}
			.description-block:first-child {
				margin-bottom: 20px;
			}
			.description {
				margin: 20px;
				width: auto;
				flex-grow: 1;
				max-width: calc(50% - 40px);
			}
			ul {
				margin: 10px;
				li {
					margin-left: 20px;
					margin-bottom: 10px;
					list-style: circle outside;
				}
			}
			a:not(.button) {
				&:hover,
				&:focus {
					text-decoration: underline;
				}
			}
			.button {
				display: inline-block;

				img {
					width: 16px;
					height: 16px;
					opacity: .5;
					margin-top: -3px;
					vertical-align: middle;
				}
			}
		}

		.content-clients {
			width: 100%;
			text-align: center;
			a {
				text-decoration: none;
				display: inline-block;
			}
			.clientslinks .appsmall {
				height: 32px;
				width: 32px;
				position: relative;
				opacity: .5;
				vertical-align: middle;
			}
			.clientslinks .button {
				display: inline-block;
				padding: 8px;
				font-weight: normal;
				font-size: 14px;
			}
		}
		.content-final {
			h3 {
				background-position: 0;
				background-size: 16px 16px;
				padding-left: 26px;
				opacity: .7;
			}
		}
		p a {
			font-weight: bold;
			color: var(--color-primary);
			&:hover,
			&:focus {
				color: var(color-text-light);
			}
		}

		.footnote {
			margin-top: 40px;
		}

		// primary on next button
		.modal-wrapper .icon-next {
			background-color: var(--color-primary);
			color: var(--color-primary-text);
			box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
			left: 22px;
		}
	}

	.clientslinks {
		margin-top: 20px;
		margin-bottom: 20px;
	}

	#wizard-values {
		list-style-type: none;
		display: flex;
		flex-wrap: wrap;
		margin: 0;
		li {
			display: block;
			min-width: 250px;
			width: 33%;
			flex-grow: 1;
			margin: 20px 0 20px 0;
			span {
				opacity: .7;
				display: block;
				height: 50px;
				width: 50px;
				background-size: 40px;
				margin: auto;
			}
			h3 {
				margin: 10px 0 10px 0;
				font-size: 130%;
				text-align: center;
			}
		}
	}

	.details-link {
		text-align: center;
	}

	@media only screen and (max-width: 680px) {
		#nmc_welcome_popup {
			.modal-container {
				max-height:420px;
				border-radius: 12px;
				.modal-footer {
					display: inline-block;
				} 
			} 
			.nmc_welcome_popup-header div.logo {
				background-size: 120px;
			}
			h2 {
				font-size: 20px;
			}
			.page > div {
				max-width: 100% !important;
				width: 100%;
			}
			.page #wizard-values li {
				min-width: 100%;
				overflow: hidden;
				display: flex;
				span {
					width: 44px !important;
					padding-right: 20px;
					flex-grow: 0;
				}
				h3 {
					font-size: 12px;
					text-align: left;
					flex-grow: 1;
				}
			}
		}
	}
</style>

<style lang="scss" scoped>	
	.modal-mask {
		background-color: rgba(0, 0, 0, 0.7);

		&::v-deep .modal-wrapper {
			position: relative;
		}

		&::v-deep .modal-container {
			display: flex;
			flex-direction: column;
			height: 95% !important;
			width: 95% !important;
			max-width: 900px;
			max-height: 650px !important;
			position: relative;
		}

		.modal-body {
			flex-grow: 1;
			// display: flex;
			overflow-x: hidden;
			overflow-y: auto;

			& > div {
				// display: flex;
				flex-grow: 1;
				align-items: center;
				justify-content: center;
			}

		}
	}


	/* Transitions */
	.next-enter-active, .next-leave-active,
	.previous-enter-active, .previous-leave-active {
		transition: transform .1s, opacity .25s;
	}

	.next-enter {
		transform: translateX(50%);
		opacity: 0;
	}

	.next-leave-to {
		transform: translateX(-50%);
		opacity: 0;
	}

	.previous-enter {
		transform: translateX(-50%);
		opacity: 0;
	}

	.previous-leave-to {
		transform: translateX(50%);
		opacity: 0;
	}
</style>

<script>
import Modal from '@nextcloud/vue/dist/Components/Modal'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import IntroVideo from './components/IntroVideo'

export default {
	name: 'App',
	components: {
		Modal,
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
				this.close()
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
