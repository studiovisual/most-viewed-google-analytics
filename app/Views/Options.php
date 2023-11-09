<?php

namespace MostViewedGoogleAnalytics\Views;

use MostViewedGoogleAnalytics\App;

defined('ABSPATH') || exit;

class Options {

	/**
	 * renderPage
	 *
	 * @return void
	 */
	public static function renderPage(): void {
		?>
		<div class="wrap">
			<h1><?php echo get_admin_page_title() ?></h1>

			<form method="post" action="options.php">
				<?php
					settings_fields(App::$domain);
					do_settings_sections(App::$domain);
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * renderInput
	 *
	 * @param  mixed $args
	 * @return void
	 */
	public static function renderInput(array $args): void {
		?>
			<input
				id="<?= $args['name'] ?>"
				name="<?= $args['name'] ?>"
				placeholder="<?= $args['placeholder'] ?>"
				value="<?= get_option($args['name'], '') ?>"
				type="text"
				style="width: 100%;"
			>
		<?php
	}

	/**
	 * renderTextarea
	 *
	 * @param  mixed $args
	 * @return void
	 */
	public static function renderTextarea(array $args): void {
		?>
			<textarea
				id="<?= $args['name'] ?>"
				name="<?= $args['name'] ?>"
				placeholder="<?= $args['placeholder'] ?>"
				cols="60"
				rows="10"
				style="width: 100%;"
			><?= get_option($args['name'], '') ?></textarea>
		<?php
	}

	/**
	 * renderTextarea
	 *
	 * @param  mixed $args
	 * @return void
	 */
	public static function renderRadio(array $args): void {
		foreach ($args['values'] as $value) {
			?>
			<input type="radio" id="<?= $value . $args['name'] ?>" name="<?= $args['name'] ?>" value="<?= $value ?>" <?= $value == get_option($args['name']) ? 'checked' : '' ?>
			<label for="<?= $value . $args['name'] ?>"><?= $value ?></label>
			<?php
		}
	}

}
