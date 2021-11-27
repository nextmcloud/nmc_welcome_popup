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

function startLoading() {
	OC.msg.startSaving('#welcome_settings_msg');
	$('#welcome_settings_loading').show();
}

function preview(setting, value, serverCssUrl) {
	OC.msg.startAction('#welcome_settings_msg', t('theming', 'Loading preview…'));
	var stylesheetsLoaded = 1;
	var reloadStylesheets = function(cssFile) {
		var queryString = '?reload=' + new Date().getTime();
		var url = cssFile + queryString;
		var old = $('link[href*="' + cssFile + '"]');
		var stylesheet = $("<link/>", {
			rel: "stylesheet",
			type: "text/css",
			href: url
		});
		stylesheet.load(function () {
			$(old).remove();
			stylesheetsLoaded--;
			if(stylesheetsLoaded === 0) {
				$('#welcome_settings_loading').hide();
				var response = { status: 'success', data: {message: t('theming', 'Saved')}};
				OC.msg.finishedSaving('#welcome_settings_msg', response);
			}
		});
		stylesheet.appendTo("head");
	};

	if (serverCssUrl !== undefined) {
		stylesheetsLoaded++;

		reloadStylesheets(serverCssUrl);
	}
	reloadStylesheets(OC.generateUrl('/apps/theming/styles'));

	if (setting === 'name') {
		window.document.title = t('core', 'Admin') + " - " + value;
	}

	//hideUndoButton(setting, value);

}

window.addEventListener('DOMContentLoaded', function () {
	$('#welcome_popup [data-toggle="tooltip"]').tooltip();

	$('#welcome_popup .welcome-undo').each(function() {
		var setting = $(this).data('setting');
		var value = $('#theming-'+setting).val();
		//hideUndoButton(setting, value);
	});

	$('.fileupload').fileupload({
		pasteZone: null,
		dropZone: null,
		done: function (e, response) {
			var $form = $(e.target).closest('form');
			var key = $form.data('image-key');

			//preview(key + 'Mime', response.result.data.name, response.result.data.serverCssUrl);
			//$form.find('.image-preview').css('backgroundImage', response.result.data.url + '?v=' + new Date().getTime());
			OC.msg.finishedSaving('#welcome_settings_msg', response.result);
			$form.find('label.button').addClass('icon-upload').removeClass('icon-loading-small');
			$form.find('.welcome-undo').show();
			$('#welcome_settings_loading').hide();
			$("#en-image-uploaded").text(response.result.data.name);
			$("#slide-image").val(response.result.data.image);
			//$('#welcome-preview-logo').css('background-image', 'url(' + response.result.data.url + ')');
			//$('#welcome-preview').css('height', '100px');
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

	// clicking preview should also trigger file upload dialog
	/*$('#welcome-preview-logo').on('click', function(e) {
		e.stopPropagation();
		$('#uploadimage').click();
	});*/
	/*$('#welcome-preview').on('click', function() {
		$('#upload-login-background').click();
	});*/

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

	function onChange(e) {
		var el = $(this);
		var setting = el.parent().find('div[data-setting]').data('setting');
		var value = $(this).val();

		if(setting === 'color') {
			if (value.indexOf('#') !== 0) {
				value = '#' + value;
			}
		}
		if(setting === 'id') {
			if(checkID()){
				$.when(el.focusout()).then(function() {
					setThemingValue('name', value);
				});
				if (e.keyCode == 13) {
					setThemingValue('name', value);
				}
			}
		}

		$.when(el.focusout()).then(function() {
			setThemingValue(setting, value);
		});
		if (e.keyCode == 13) {
			setThemingValue(setting, value);
		}
	};

	$('#theming input[type="text"]').change(onChange);
	$('#theming input[type="url"]').change(onChange);

	$('.welcome-undo').click(function (e) {
		var setting = $(this).data('setting');
		var $form = $(this).closest('form');
		var image = $form.data('image-key');

		startLoading();
		$('.welcome-undo[data-setting=' + setting + ']').hide();
		$.post(
			OC.generateUrl('/apps/theming/ajax/undoChanges'), {'setting' : setting}
		).done(function(response) {
			if (setting === 'color') {
				var colorPicker = document.getElementById('theming-color');
				colorPicker.style.backgroundColor = response.data.value;
				colorPicker.value = response.data.value.slice(1).toUpperCase();
			} else if (!image) {
				var input = document.getElementById('theming-'+setting);
				input.value = response.data.value;
			}
			preview(setting, response.data.value, response.data.serverCssUrl);
		});
	});

	$('.welcome-remove-bg').click(function() {
		startLoading();
		$.post(
			OC.generateUrl('/apps/theming/ajax/updateStylesheet'), {'setting' : 'backgroundMime', 'value' : 'backgroundColor'}
		).done(function(response) {
			preview('backgroundMime', 'backgroundColor', response.data.serverCssUrl);
		}).fail(function(response) {
			OC.msg.finishedSaving('#welcome_settings_msg', response);
			$('#welcome_settings_loading').hide();
		});
	});
	
	$('#add_new_popup').click(function() {
		var en_title = $('#en-slide-title').val();
		var en_primaryBtnLbl = $('#en-primary-button-label').val();
		var en_primaryBtnUrl = $('#en-primary-button-url').val();
		var en_secondaryBtnDesc = $('#en-secondary-button-desc').val();
		var en_displayPbty = $('#en-display-probability').val();
		var en_text = $('#en-text').val();
		
		var du_title = $('#du-slide-title').val();
		var du_primaryBtnLbl = $('#du-primary-button-label').val();
		var du_primaryBtnUrl = $('#du-primary-button-url').val();
		var du_secondaryBtnDesc = $('#du-secondary-button-desc').val();
		var du_displayPbty = $('#du-display-probability').val();
		var du_text = $('#du-text').val();

		var image_name = $('#slide-image').val();

		startLoading();
		$.post(
			OC.generateUrl('/apps/nmc_welcome_popup/ajax/addSlide'), {'slide': {'en_GB' : {'title' : en_title, 'primary_button_label' : en_primaryBtnLbl, 'primary_button_url' : en_primaryBtnUrl, 'secondary_button_desc' : en_secondaryBtnDesc, 'display_probability' : en_displayPbty, 'content': en_text}, 'de_DE' : {'title' : du_title, 'primary_button_label' : du_primaryBtnLbl, 'primary_button_url' : du_primaryBtnUrl, 'secondary_button_desc' : du_secondaryBtnDesc, 'display_probability' : du_displayPbty, 'content': du_text},'image_uploaded' : image_name}}
		).done(function(response) {
			//hideUndoButton(setting, value);
			//preview(setting, value, response.data.serverCssUrl);
			OC.msg.finishedSaving('#welcome_settings_msg', response);
			$('#welcome_settings_loading').hide();
		}).fail(function(response) {
			OC.msg.finishedSaving('#welcome_settings_msg', response.responseJSON);
			$('#welcome_settings_loading').hide();
		});
	});

});
