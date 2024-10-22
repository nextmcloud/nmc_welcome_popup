<?php
/**
 *
 *
 * @author Bjoern Schiessle <bjoern@schiessle.org>
 * @author Jan-Christoph Borchardt <hey@jancborchardt.net>
 * @author Lukas Reschke <lukas@statuscode.ch>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
style('nmc_welcome_popup', 'admin-settings');
script('nmc_welcome_popup', 'nmc_welcome_popup-admin-settings');

$en = 'en_GB';
$de = 'de_DE';

$_['image_uploaded'] = isset($_['image_uploaded']) ? $_['image_uploaded'] : "";
$_['image_url'] = isset($_['image_url']) ? $_['image_url'] : "";

$_[$de]['title'] = isset($_[$de]['title']) ? $_[$de]['title'] : "";
$_[$de]['primary_button_label'] = isset($_[$de]['primary_button_label']) ? $_[$de]['primary_button_label'] : "";
$_[$de]['primary_button_url'] = isset($_[$de]['primary_button_url']) ? $_[$de]['primary_button_url'] : "";
$_[$de]['secondary_button_desc'] = isset($_[$de]['secondary_button_desc']) ? $_[$de]['secondary_button_desc'] : "";
$_[$de]['content'] = isset($_[$de]['content']) ? $_[$de]['content'] : "";

$_[$en]['title'] = isset($_[$en]['title']) ? $_[$en]['title'] : "";
$_[$en]['primary_button_label'] = isset($_[$en]['primary_button_label']) ? $_[$en]['primary_button_label'] : "";
$_[$en]['primary_button_url'] = isset($_[$en]['primary_button_url']) ? $_[$en]['primary_button_url'] : "";
$_[$en]['secondary_button_desc'] = isset($_[$en]['secondary_button_desc']) ? $_[$en]['secondary_button_desc'] : "";
$_[$en]['content'] = isset($_[$en]['content']) ? $_[$en]['content'] : "";

$_['slide_ids'] = (isset($_['slide_ids']) && is_array($_['slide_ids'])) ? $_['slide_ids'] : [1];
$length = count($_['slide_ids']);

if ($length >= 5) {
	$displayAddBtnStyle = "display: none";
} else {
	$displayAddBtnStyle = '';
}

?>
<div id="welcome_popup" class="section">
	<h2 class="inlineblock"><?php p($l->t('Create a new welcome pop-up')); ?></h2>
	<ul class="slide-list settings-hint">
		<?php for ($id = 0; $id < $length; $id++) { ?>
		<li data-id="<?php p($_['slide_ids'][$id]); ?>">
			<a href="#">Slide <?php p($id + 1); ?></a>
		</li>
		<?php } ?>
		<li class="add-slide" style="<?php p($displayAddBtnStyle); ?>"><a href="#" class="button"><span class="icon-add"></span></a></li>
	</ul>
	<div>
		<div id="welcome_img_loading" class="icon-loading-small" style="display: none;"></div>
		<span id="welcome_img_loaded_msg" class="msg success" style="display: none;">Saved</span>
	</div>
	<p>
		<?php p($_['errorMessage']) ?>
	</p>
	<div>
		<form class="uploadButton" method="post" action="<?php p($_['uploadImageRoute']) ?>" data-image-key="image">
			<input type="hidden" id="slide-image" data-imgurl="<?php p($_['image_url']); ?>" value="<?php p($_['image_uploaded']); ?>" />
			<input type="hidden" name="key" id="image-name" data-key="welcome_image" value="welcome_image_<?php p($_['slide_ids'][0]); ?>" />
			<label for="uploadimage"><span style="min-width: 50px;">Image</span></label>
			<input id="uploadimage" class="fileupload" name="image" type="file" />
			<div class="image-label">
				<label for="uploadimage" class="button icon-upload svg" id="uploadimage" title="<?php p($l->t('Header image for the pop-up')) ?>"></label>
				<label id="image-uploaded"></label>
				<a id="remove-img" class="icon-delete" style="display: none;"></a>
			</div>
		</form>
	</div>
</div>
<div id="welcome_popup" class="section section-deutsch" style="display: inline-block">
	<h3 class="inlineblock">Deutsch</h3>
	<div>
		<label>
			<div>Titel</div>
			<input id="<?php p($de) ?>-slide-title" type="text" data-key="<?php p($de) ?>_title" placeholder="Überschrift für das Pop-up" value="<?php p($_[$de]['title']) ?>" maxlength="250" />
		</label>
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<label>
			<div>Buttons</div>
			<input id="<?php p($de) ?>-primary-button-label" type="text" data-key="<?php p($de) ?>_primary_button_label" placeholder="Bezeichnung für den primären Button" value="<?php p($_[$de]['primary_button_label']) ?>" maxlength="250" />
		</label>
	</div>
	<div>
		<label>
			<input id="<?php p($de) ?>-primary-button-url" type="text" data-key="<?php p($de) ?>_primary_button_url" placeholder="Link für den primären Button" value="<?php p($_[$de]['primary_button_url']) ?>" maxlength="500" />
		</label>
	<div>
		<label>
			<input id="<?php p($de) ?>-secondary-button-desc" type="text" data-key="<?php p($de) ?>_secondary_button_desc" placeholder="Bezeichnung für den sekundären Button zum Schließen" value="<?php p($_[$de]['secondary_button_desc']) ?>" maxlength="500" />
		</label>
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<label>
			<div>Text</div>
			<textarea id="<?php p($de) ?>-text" data-key="<?php p($de) ?>_content" placeholder="HTML interpretierender Text für das Pop-up" rows="16" cols="48" maxlength="1000"><?php p($_[$de]['content']) ?></textarea>
		</label>
	</div>
	<div>
		<input type="button" id="<?php p($de) ?>-show-preview" class="show-preview" data-lang="<?php p($de) ?>" value="Vorschau" style="width: auto;" />
	</div>
</div>
</div>
<div id="welcome_popup" class="section section-english" style="display: inline-block">
	<h2 class="inlineblock"></h2>
	<h3 class="inlineblock">English</h3>
	<div>
		<label>
			<div>Title</div>
			<input id="<?php p($en) ?>-slide-title" type="text" data-key="<?php p($en) ?>_title" placeholder="Headline for the pop-up" value="<?php p($_[$en]['title']) ?>" maxlength="250" />
		</label>
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<label>
			<div>Buttons</div>
			<input id="<?php p($en) ?>-primary-button-label" type="text" data-key="<?php p($en) ?>_primary_button_label" placeholder="Primary button label" value="<?php p($_[$en]['primary_button_label']) ?>" maxlength="250" />
		</label>
	</div>
	<div>
		<label>
			<input id="<?php p($en) ?>-primary-button-url" type="text" data-key="<?php p($en) ?>_primary_button_url" placeholder="URL for primary button" value="<?php p($_[$en]['primary_button_url']) ?>" maxlength="500" />
		</label>
	<div>
		<label>
			<input id="<?php p($en) ?>-secondary-button-desc" type="text" data-key="<?php p($en) ?>_secondary_button_desc" placeholder="Secondary button description to close the pop-up" value="<?php p($_[$en]['secondary_button_desc']) ?>" maxlength="500" />
		</label>
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<label>
			<div>Text</div>
			<textarea id="<?php p($en) ?>-text" data-key="<?php p($en) ?>_content" placeholder="HTML interpreting text for the pop-up" rows="16" cols="48" maxlength="1000"><?php p($_[$en]['content']) ?></textarea>
		</label>
	</div>
	<div>
		<input type="button" id="<?php p($en) ?>-show-preview" class="show-preview" data-lang="<?php p($en) ?>" value="Preview" style="width: auto;" />
	</div>
</div>
</div>
<div class="section" style="padding-top: 0px;">
	<h4 class="inlineblock"></h4>
	<div>
		<span id="remove_slide_msg" class="msg success" style="display: none;">Saved</span>
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<input type="button" id="remove_slide" value="<?php p($l->t('Remove slide')); ?>" />
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<div id="welcome_settings_loading" class="icon-loading-small" style="display: none;"></div>
		<span id="welcome_settings_msg" class="msg success" style="display: none;">Saved</span>
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<input type="button" id="add_new_popup" value="<?php p($l->t('Save pop-up')); ?>" />
	</div>
</div>