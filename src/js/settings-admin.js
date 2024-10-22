/**
 * @author Björn Schießle <bjoern@schiessle.org>
 *
 * @copyright Copyright (c) 2016, Bjoern Schiessle
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your opinion) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

var imgUrl;
var en = 'en_GB';
var de = 'de_DE';

(function(OCA) {
	OCA.NMC_Welcome_Popup = OCA.NMC_Welcome_Popup || {};

	/**
	 * @namespace OCA.NMC_Welcome_Popup.Admin
	 */
	OCA.NMC_Welcome_Popup.Admin = {
		currentConfig: '1',
		slideIds: '1',
		noSlides: '0',
		maxSlides: '5',

		_getAppConfig: function (key) {
			return $.ajax({
				type: 'GET',
				url: OC.linkToOCS('apps/provisioning_api/api/v1', 2) + 'config/apps' + '/nmc_welcome_popup/' + key + '?format=json'
			});
		},
		init: function(callback) {
			this._getAppConfig('slideIds').done(function (data){
				if (data.ocs.data.data !== '') {
					OCA.NMC_Welcome_Popup.Admin.slideIds = data.ocs.data.data;
					OCA.NMC_Welcome_Popup.Admin.currentConfig = OCA.NMC_Welcome_Popup.Admin.slideIds.split(',')[0];
				} else {
					OCA.NMC_Welcome_Popup.Admin.noSlides = '1';
				}
				callback();
			});
		},

		/**
		 * Add a new slide
		 * @returns {number} id of the slide
		 */
		addSlide: function(callback) {
			var slideIds = OCA.NMC_Welcome_Popup.Admin.slideIds.split(',');
			if (slideIds.length < OCA.NMC_Welcome_Popup.Admin.maxSlides) {
				var nextId = 1;
				if (slideIds.indexOf('1') >= 0) {
					nextId = 2;
					while ($.inArray('' + nextId, slideIds) >= 0) {
						nextId++;
					}
				}
				OCP.AppConfig.setValue('nmc_welcome_popup', 'slideIds', OCA.NMC_Welcome_Popup.Admin.slideIds + ',' + nextId, {
					success: function () {
						OCA.NMC_Welcome_Popup.Admin.slideIds += ',' + nextId;
						OCA.NMC_Welcome_Popup.Admin.noSlides = '0';
						callback(nextId)
					}
				});
			}
		},

		removeSlide: function(callback) {
			if (OCA.NMC_Welcome_Popup.Admin.noSlides === '0') {
				var slideIds = OCA.NMC_Welcome_Popup.Admin.slideIds.split(',');
				if (slideIds.length >= 1) {
					var index = slideIds.indexOf(this.currentConfig);
					if (index > -1) {
						slideIds.splice(index, 1);
					}
					var config = this.currentConfig;
					$.ajax({ url: OC.generateUrl('/apps/nmc_welcome_popup/ajax/slideSettings/' + this.currentConfig), type: 'DELETE'})
						.done(function(data) {
							OCP.AppConfig.setValue('nmc_welcome_popup', 'slideIds', slideIds.join(','), {
								success: function () {
									if (slideIds.length > 0) {
										OCA.NMC_Welcome_Popup.Admin.slideIds = slideIds.join(',');
									} else {
										OCA.NMC_Welcome_Popup.Admin.noSlides = '1';
										OCA.NMC_Welcome_Popup.Admin.slideIds = '1';
									}
									callback(config);
								}
							});
						});
				}
			}
		},
	}
})(OCA);

function startLoading() {
	OC.msg.startSaving('#welcome_settings_msg');
	$('#welcome_settings_loading').show();
}

function startLoadingImg() {
	// OC.msg.startSaving('#welcome_img_loaded_msg');
	if ($('#welcome_img_loaded_msg').is(":hidden") == false) {
		$('#welcome_img_loaded_msg').hide();
	}
	$('#welcome_img_loading').show();
}

$(function() {

	// $('#welcome_popup [data-toggle="tooltip"]').tooltip();

	var addSlideBtn = $('.slide-list .add-slide');
	var slideDataId = '.slide-list li[data-id=';

	if ($('#slide-image').val()) {
		$('#remove-img').show();
	} else {
		$('#remove-img').hide();
	}

	if(!imgUrl) {
		imgUrl = $('#slide-image').data('imgurl');
		$('#slide-image').data('imgurl', '');
	}

	$('#remove-img').click(function (e) {
		var image_name = $('#slide-image').val();
		var slideId = $('.slide-list .active').data('id');

		startLoadingImg();
		$.ajax({
			type: "DELETE",
			url: OC.generateUrl('/apps/nmc_welcome_popup/image/' + image_name),
			data: {'slideId' : slideId}
		}).done(function(response) {
			OC.msg.finishedSaving('#welcome_img_loaded_msg', response);
			$('#welcome_img_loading').hide();
			$("#slide-image").val("");
			$("#image-uploaded").text("");
			$('#remove-img').hide();
		}).fail(function(response) {
			OC.msg.finishedError('#welcome_img_loaded_msg', response.responseJSON.status);
			$('#welcome_img_loading').hide();
		});
	});

	$('.fileupload').fileupload({
		pasteZone: null,
		dropZone: null,
		done: function (e, response) {
			var $form = $(e.target).closest('form');
			var key = $form.data('image-key');

			// OC.msg.finishedSaving('#welcome_img_loaded_msg', response.result);
			$form.find('label.button').addClass('icon-upload').removeClass('icon-loading-small');
			$('#welcome_img_loading').hide();
			$("#image-uploaded").text(response.result.data.name);
			$("#slide-image").val(response.result.data.image);
			imgUrl = response.result.data.url;
			// $('#remove-img').show();
		},
		submit: function(e, response) {
			var $form = $(e.target).closest('form');
			var key = $form.data('image-key');
			startLoadingImg();
			$form.find('label.button').removeClass('icon-upload').addClass('icon-loading-small');
		},
		fail: function (e, response){
			var $form = $(e.target).closest('form');
			OC.msg.finishedError('#welcome_img_loaded_msg', response._response.jqXHR.responseJSON.data.message);
			$form.find('label.button').addClass('icon-upload').removeClass('icon-loading-small');
			$('#welcome_img_loading').hide();
		}
	});
	
	$('#add_new_popup').click(function() {

		var slideId = $('.slide-list .active').data('id');

		startLoading();
		$.post(
			OC.generateUrl('/apps/nmc_welcome_popup/ajax/slideSettings/' + slideId), getParams()
		).done(function(response) {
			OC.msg.finishedSaving('#welcome_settings_msg', response);
			$('#welcome_settings_loading').hide();
			if ($('#slide-image').val()) {
				$('#remove-img').show();
			}
		}).fail(function(response) {
			OC.msg.finishedError('#welcome_settings_msg', response.responseJSON.data.message);
			$('#welcome_settings_loading').hide();
		});
	});

	$('.show-preview').click(function(event) {
		var lang = $(this).data('lang');
		event.stopPropagation();
		event.preventDefault();
		var slide_params = getParams().slide;
		var image_name = slide_params.image_uploaded;
		if (!image_name) {
			imgUrl = "";
		} else if(!imgUrl) {
			var cachebusterImg = Math.round(new Date().getTime() / 1000);
			imgUrl = OC.generateUrl('/apps/nmc_welcome_popup/image/' + image_name) + '?v=' + cachebusterImg;
		}
		var langSlide = slide_params[lang];
		langSlide.image_url = imgUrl;
		OCP.Loader.loadScript('nmc_welcome_popup', 'nmc_welcome_popup-main.mjs').then(function () {
			window.OCA.NMC_Welcome_Popup.NcModal.previewSlide([langSlide]);
		});
		return false;
	});

	function getParams() {
		var en_title = $('#' + en + '-slide-title').val();
		var en_primaryBtnLbl = $('#' + en + '-primary-button-label').val();
		var en_primaryBtnUrl = $('#' + en + '-primary-button-url').val();
		var en_secondaryBtnDesc = $('#' + en + '-secondary-button-desc').val();
		var en_text = $('#' + en + '-text').val();
		
		var de_title = $('#' + de + '-slide-title').val();
		var de_primaryBtnLbl = $('#' + de + '-primary-button-label').val();
		var de_primaryBtnUrl = $('#' + de + '-primary-button-url').val();
		var de_secondaryBtnDesc = $('#' + de + '-secondary-button-desc').val();
		var de_text = $('#' + de + '-text').val();

		var image_name = $('#slide-image').val();

		return {'slide': {'en_GB' : {'title' : en_title, 'primary_button_label' : en_primaryBtnLbl, 'primary_button_url' : en_primaryBtnUrl, 'secondary_button_desc' : en_secondaryBtnDesc, 'content': en_text}, 'de_DE' : {'title' : de_title, 'primary_button_label' : de_primaryBtnLbl, 'primary_button_url' : de_primaryBtnUrl, 'secondary_button_desc' : de_secondaryBtnDesc, 'content': de_text},'image_uploaded' : image_name}};
	}

	OCA.NMC_Welcome_Popup.Admin.init(function() {
		$(slideDataId + '"' + OCA.NMC_Welcome_Popup.Admin.currentConfig + '"]').addClass('active');
	});

	var switchSlide = function(slideId) {
		$('.slide-list li').removeClass('active');
		$(slideDataId + '"' + slideId + '"]').addClass('active');
		if ($('#welcome_img_loaded_msg').is(":hidden") == false) {
			$('#welcome_img_loaded_msg').hide();
		}
		if ($('#welcome_settings_msg').is(":hidden") == false) {
			$('#welcome_settings_msg').hide();
		}
		OCA.NMC_Welcome_Popup.Admin.currentConfig = '' + slideId;
		$.get(OC.generateUrl('/apps/nmc_welcome_popup/ajax/slideSettings/' + slideId)).done(function(data) {
			if (Object.entries(data).length > 0) {
				Object.keys(data).forEach(function(form_section){
					if (form_section == en || form_section == de) {
						var entries = data[form_section];
						Object.keys(entries).forEach(function (configKey) {
							var element = $('#welcome_popup *[data-key="' + form_section + '_' + configKey + '"]');
							if(element.is('input') && (element.prop('type') === 'text' || element.prop('type') === 'number' || element.prop('type') === 'url')) {
								element.val(entries[configKey])
							}
							else if(element.is('textarea')) {
								element.val(entries[configKey]);
							}
							else if(element.prop('type') === 'checkbox') {
								var value = entries[configKey] === '1' ? '1' : '0';
								element.val(value);
							} else {
								console.log('unable to find element for ' + configKey);
							}
						});
					} else if (form_section == 'image_uploaded') {
						$("#slide-image").val(data[form_section]);
						if ($('#slide-image').val()) {
							$('#remove-img').show();
						} else {
							$('#remove-img').hide();
						}
					} else if (form_section == 'image_url') {
						imgUrl = data[form_section];
					}
				});
			} else {
				var element = $('#welcome_popup *[data-key]');
				element.val("");
				$("#slide-image").val("");
				$('#remove-img').hide();
			}
			var imgName = $("#image-name");
			imgName.val(imgName.data('key') + '_' + slideId);
			$("#image-uploaded").text("");
			$('input:checkbox[value="1"]').attr('checked', true);
			$('input:checkbox[value="0"]').attr('checked', false);
		});
	};

	addSlideBtn.on('click', function() {
		OCA.NMC_Welcome_Popup.Admin.addSlide(function (nextId) {
			var slideIds = OCA.NMC_Welcome_Popup.Admin.slideIds.split(',');
			$('<li data-id="' + nextId + '"><a>Slide ' + slideIds.length + '</a></li>').insertBefore(addSlideBtn[0]);
			switchSlide(nextId);
			if (slideIds.length >= OCA.NMC_Welcome_Popup.Admin.maxSlides) {
				addSlideBtn.hide();
			}
		});
	});

	$('.slide-list').on('click', 'li:not(.add-slide)', function() {
		var slideId = '' + $(this).data('id');
		switchSlide(slideId);
	});

	$('#remove_slide').on('click', function() {
		OCA.NMC_Welcome_Popup.Admin.removeSlide(function(currentConfig) {
			OC.msg.finishedSuccess('#remove_slide_msg', "Slide removed");
			var slideIds = OCA.NMC_Welcome_Popup.Admin.slideIds.split(',');
			if (OCA.NMC_Welcome_Popup.Admin.noSlides === '0') {
				$(slideDataId + '"' + currentConfig + '"]').remove();
				renameSlides(slideIds);
				switchSlide(slideIds[0]);
			} else {
				$(slideDataId + '"' + currentConfig + '"]').attr('data-id', slideIds[0]);
				switchSlide(slideIds[0]);
			}
			if (addSlideBtn.is(":hidden")) {
				addSlideBtn.show();
			}
		});
	});

	function renameSlides(slideIds) {
		for (id = 0; id < slideIds.length; id++) {
			$(slideDataId +'"' + slideIds[id] + '"] a').text(function(_,text){
				return "Slide " + (id + 1);
			});
		}
	}

});
