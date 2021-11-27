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
script('nmc_welcome_popup', 'settings-admin');
style('nmc_welcome_popup', 'settings-admin');

$en = 'en_GB';
$du = 'de_DE';

?>
<div id="welcome_popup" class="section" style="display: inline-block">
	<h2 class="inlineblock"><?php p($l->t('Create a new welcome pop-up')); ?></h2>
	<p class="settings-hint"><?php p($l->t('Slide 1')); ?></p>
	<div>
		<div id="welcome_settings_loading" class="icon-loading-small" style="display: none;"></div>
		<span id="welcome_settings_msg" class="msg success" style="display: none;">Saved</span>
	</div>
	<p>
		<?php p($_['errorMessage']) ?>
	</p>
	<div>
		<form class="uploadButton" method="post" action="<?php p($_['uploadImageRoute']) ?>" data-image-key="image">
			<input type="hidden" id="slide-image" value="<?php p($_['image_uploaded']); ?>" />
			<input type="hidden" name="key" value="welcome_image" />
			<label for="en-uploadimage"><span style="min-width: 50px;">Image</span></label>
			<input id="en-uploadimage" class="fileupload" name="image" type="file" />
			<label for="en-uploadimage" class="button icon-upload svg" id="en-uploadimage" title="<?php p($l->t('Header image for the pop-up')) ?>"></label>
			<label id="en-image-uploaded"></label>
		</form>
	</div>
	<!--<div id="welcome-preview">
		<div id="welcome-preview-logo"></div>
	</div>-->
	<h3 class="inlineblock">Deutsch</h3>
	<div>
		<label>
			<div>Titel</div>
			<input id="du-slide-title" type="text" placeholder="Überschrift für das Pop-up" value="<?php p($_[$du]['title']) ?>" maxlength="250" />
		</label>
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<label>
			<div>Buttons</div>
			<input id="du-primary-button-label" type="text" placeholder="Bezeichnung für den primären Button" value="<?php p($_[$du]['primary_button_label']) ?>" maxlength="250" />
		</label>
	</div>
	<div>
		<label>
			<input id="du-primary-button-url" type="url" placeholder="Link für den primären Button" value="<?php p($_[$du]['primary_button_url']) ?>" maxlength="500" />
		</label>
	<div>
		<label>
			<input id="du-secondary-button-desc" type="text" placeholder="Bezeichnung für den sekundären Button zum Schließen" value="<?php p($_[$du]['secondary_button_desc']) ?>" maxlength="500" />
		</label>
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<label>
			<div>Anzeigenwahrscheinlichkeit</div>
			<input id="du-display-probability" type="number" placeholder="Wahrscheinlichkeit zwischen 1 und 100" maxlength="3" value="<?php p($_[$du]['display_probability']) ?>" />
		</label>
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<label>
			<div>Text</div>
			<textarea id="du-text" placeholder="HTML interpretierender Text für das Pop-up" rows="16" cols="48" maxlength="500"><?php p($_[$du]['content']) ?></textarea>
		</label>
	</div>
</div>
</div>

<div id="welcome_popup" class="section" style="display: inline-block">
	<h2 class="inlineblock"></h2>
	<p class="settings-hint"></p>
	<div>
		<div id="welcome_settings_loading" class="icon-loading-small" style="display: none;"></div>
		<span id="welcome_settings_msg" class="msg success" style="display: none;">Saved</span>
	</div>
	<p>
		<?php p($_['errorMessage']) ?>
	</p>
	<h3 class="inlineblock">English</h3>
	<div>
		<label>
			<div>Title</div>
			<input id="en-slide-title" type="text" placeholder="Headline for the pop-up" value="<?php p($_[$en]['title']) ?>" maxlength="250" />
		</label>
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<label>
			<div><?php p($l->t('Buttons')) ?></div>
			<input id="en-primary-button-label" type="text" placeholder="Primary button label" value="<?php p($_[$en]['primary_button_label']) ?>" maxlength="250" />
		</label>
	</div>
	<div>
		<label>
			<input id="en-primary-button-url" type="url" placeholder="URL for primary button" value="<?php p($_[$en]['primary_button_url']) ?>" maxlength="500" />
		</label>
	<div>
		<label>
			<input id="en-secondary-button-desc" type="text" placeholder="Secondary button description to close the pop-up" value="<?php p($_[$en]['secondary_button_desc']) ?>" maxlength="500" />
		</label>
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<label>
			<div><?php p($l->t('Display probability')) ?></div>
			<input id="en-display-probability" type="number" placeholder="Probability between 1 and 100" maxlength="3" value="<?php p($_[$en]['display_probability']) ?>" />
		</label>
	</div>
	<h4 class="inlineblock"></h4>
	<div>
		<label>
			<div><?php p($l->t('Text')) ?></div>
			<textarea id="en-text" placeholder="HTML interpreting text for the pop-up" rows="16" cols="48" maxlength="500"><?php p($_[$en]['content']) ?></textarea>
		</label>
	</div>
</div>
</div>
<div class="section" style="padding-top: 0px;">
	<h4 class="inlineblock"></h4>
	<div>
		<input type="button" id="add_new_popup" value="<?php p($l->t('Save pop-up')); ?>" />
	</div>
</div>