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
					OCA.NMC_Welcome_Popup.Admin.currentConfig = OCA.NMC_Welcome_Popup.Admin.slideIds.split(',').sort()[0];
				}
				callback();
			});
		},
		getConfigIdentifier: function() {
			if (this.currentConfig === '1') {
				return '';
			}
			return this.currentConfig + '-';
		},

		/**
		 * Add a new provider
		 * @returns {number} id of the provider
		 */
		addSlide: function(callback) {
			var slideIds = OCA.NMC_Welcome_Popup.Admin.slideIds.split(',');
			var nextId = 1;
			if (slideIds.indexOf('1') >= 0) {
				nextId = 2;
				while ($.inArray('' + nextId, slideIds) >= 0) {
					nextId++;
				}
			}
			// console.log('Next ID: ' + nextId);
			OCP.AppConfig.setValue('nmc_welcome_popup', 'slideIds', OCA.NMC_Welcome_Popup.Admin.slideIds + ',' + nextId, {
				success: function () {
					OCA.NMC_Welcome_Popup.Admin.slideIds += ',' + nextId;
					callback(nextId)
				}
			});
			// console.log('Next ID after save: ' + nextId);
		},

		removeSlide: function(callback) {
			var slideIds = OCA.NMC_Welcome_Popup.Admin.slideIds.split(',');
			if (slideIds.length > 1) {
				var index = slideIds.indexOf(this.currentConfig);
				if (index > -1) {
					slideIds.splice(index, 1);
				}
				var config = this.currentConfig;
				$.ajax({ url: OC.generateUrl('/apps/nmc_welcome_popup/ajax/removeSlide/' + this.currentConfig), type: 'DELETE'})
					.done(function(data) {
						OCP.AppConfig.setValue('nmc_welcome_popup', 'slideIds', slideIds.join(','), {
							success: function () {
								OCA.NMC_Welcome_Popup.Admin.slideIds = slideIds.join(',');
								callback(config);
							}
						});
					});

			}
		},
	}
})(OCA);

function startLoading() {
	OC.msg.startSaving('#welcome_settings_msg');
	$('#welcome_settings_loading').show();
}

$(function() {

	$('#welcome_popup [data-toggle="tooltip"]').tooltip();

	if ($('#slide-image').val()) {
		$('#remove-img').show();
	} else {
		$('#remove-img').hide();
	}

	$('#remove-img').click(function (e) {
		var image_name = $('#slide-image').val();
		var slideId = $('.slide-list .active').data('id');

		startLoading();
		$.ajax({
			type: "DELETE",
			url: OC.generateUrl('/apps/nmc_welcome_popup/image/' + image_name),
			data: {'slideId' : slideId}
		}).done(function(response) {
			OC.msg.finishedSaving('#welcome_settings_msg', response);
			$('#welcome_settings_loading').hide();
			$("#slide-image").val("");
			$("#en-image-uploaded").text("");
			$('#remove-img').hide();
		}).fail(function(response) {
			OC.msg.finishedError('#welcome_settings_msg', response.responseJSON.status);
			$('#welcome_settings_loading').hide();
		});
	});

	$('.fileupload').fileupload({
		pasteZone: null,
		dropZone: null,
		done: function (e, response) {
			var $form = $(e.target).closest('form');
			var key = $form.data('image-key');

			OC.msg.finishedSaving('#welcome_settings_msg', response.result);
			$form.find('label.button').addClass('icon-upload').removeClass('icon-loading-small');
			$('#welcome_settings_loading').hide();
			$("#en-image-uploaded").text(response.result.data.name);
			$("#slide-image").val(response.result.data.image);
			$('#remove-img').show();
		},
		submit: function(e, response) {
			var $form = $(e.target).closest('form');
			var key = $form.data('image-key');
			startLoading();
			$form.find('label.button').removeClass('icon-upload').addClass('icon-loading-small');
		},
		fail: function (e, response){
			var $form = $(e.target).closest('form');
			OC.msg.finishedError('#welcome_settings_msg', response._response.jqXHR.responseJSON.data.message);
			$form.find('label.button').addClass('icon-upload').removeClass('icon-loading-small');
			$('#welcome_settings_loading').hide();
		}
	});

	function checkID () {
		var length = $('#slide-id').val().length;
		try {
			if (length > 0) {
				return true;
			} else {
				throw t('theming', 'ID cannot be empty');
			}
		} catch (error) {
			$('#slide-id').attr('title', error);
			$('#slide-id').tooltip({placement: 'top', trigger: 'manual'});
			$('#slide-id').tooltip('fixTitle');
			$('#slide-id').tooltip('show');
			$('#slide-id').addClass('error');
		}
		return false;
	}

	$('#slide-id').keyup(function() {
		if (checkID()) {
			$('#slide-id').tooltip('hide');
			$('#slide-id').removeClass('error');
		}
	});

	$('#slide-id').change(function(e) {
		var el = $(this);
	});
	
	$('#add_new_popup').click(function() {
		// var en = 'en_GB';
		// var de = 'de_DE';

		var slideId = $('.slide-list .active').data('id');

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

		startLoading();
		$.post(
			OC.generateUrl('/apps/nmc_welcome_popup/ajax/slideSettings/' + slideId), {'slide': {'en_GB' : {'title' : en_title, 'primary_button_label' : en_primaryBtnLbl, 'primary_button_url' : en_primaryBtnUrl, 'secondary_button_desc' : en_secondaryBtnDesc, 'content': en_text}, 'de_DE' : {'title' : de_title, 'primary_button_label' : de_primaryBtnLbl, 'primary_button_url' : de_primaryBtnUrl, 'secondary_button_desc' : de_secondaryBtnDesc, 'content': de_text},'image_uploaded' : image_name}}
		).done(function(response) {
			OC.msg.finishedSaving('#welcome_settings_msg', response);
			$('#welcome_settings_loading').hide();
		}).fail(function(response) {
			OC.msg.finishedSaving('#welcome_settings_msg', response.responseJSON);
			$('#welcome_settings_loading').hide();
		});
	});

	OCA.NMC_Welcome_Popup.Admin.init(function() {
		$('.slide-list li[data-id="' + OCA.NMC_Welcome_Popup.Admin.currentConfig + '"]').addClass('active');
		// if (OCA.NMC_Welcome_Popup.Admin.slideIds.split(',').length <= 1) {
		// 	$('[data-js="remove-idp"]').addClass('hidden');
		// }
	});

	var switchSlide = function(slideId) {
		$('.slide-list li').removeClass('active');
		$('.slide-list li[data-id="' + slideId + '"]').addClass('active');
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
					} else if (form_section =='image_uploaded') {
						$("#slide-image").val(data[form_section]);
						if ($('#slide-image').val()) {
							$('#remove-img').show();
						} else {
							$('#remove-img').hide();
						}
					}
				});
			} else {
				var element = $('#welcome_popup *[data-key]');
				element.val("");
				$("#slide-image").val("");
				$('#remove-img').hide();
			}
			$('input:checkbox[value="1"]').attr('checked', true);
			$('input:checkbox[value="0"]').attr('checked', false);
		});
	};

	$('.slide-list .add-slide').on('click', function() {
		OCA.NMC_Welcome_Popup.Admin.addSlide(function (nextId) {
			$('<li data-id="' + nextId + '"><a>Slide ' + nextId + '</a></li>').insertBefore('.slide-list .add-slide');
			// console.log('Next slide inserted: ' + nextId);
			switchSlide(nextId);
			// $('[data-js="remove-idp"]').removeClass('hidden');
		});
	});

	$('.slide-list').on('click', 'li:not(.add-slide)', function() {
		var slideId = '' + $(this).data('id');
		switchSlide(slideId);
	});

});
