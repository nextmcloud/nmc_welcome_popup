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

window.addEventListener('DOMContentLoaded', function () {
	$('#welcome_popup [data-toggle="tooltip"]').tooltip();

	if ($('#slide-image').val()) {
		$('#remove-img').show();
	} else {
		$('#remove-img').hide();
	}

	$('#remove-img').click(function (e) {
		var image_name = $('#slide-image').val();

		startLoading();
		$.ajax({
			type: "DELETE",
			url: OC.generateUrl('/apps/nmc_welcome_popup/image/' + image_name),
			data: {'image_uploaded' : image_name}
		}).done(function(response) {
			OC.msg.finishedSaving('#welcome_settings_msg', response);
			$('#welcome_settings_loading').hide();
			$("#slide-image").val("");
			$("#en-image-uploaded").text("");
			$('#remove-img').hide();
		}).fail(function(response) {
			OC.msg.finishedError('#welcome_settings_msg', response.responseJSON);
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
			OC.msg.finishedSaving('#welcome_settings_msg', response);
			$('#welcome_settings_loading').hide();
		}).fail(function(response) {
			OC.msg.finishedSaving('#welcome_settings_msg', response.responseJSON);
			$('#welcome_settings_loading').hide();
		});
	});

});
