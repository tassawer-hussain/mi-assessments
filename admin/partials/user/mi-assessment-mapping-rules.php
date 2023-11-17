<?php
/**
 * Mapping page template
 *
 * This file is used to markup the Mapping page template.
 *
 * @link       https://ministryinsights.com/
 * @since      2.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/admin/partials/user
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php

$map_count = 0;

$map_count_loop = ( ( isset( $map_data['instrument_id'][0] ) && ! empty( $map_data['instrument_id'][0] ) ) || ( isset( $map_data['instrument_id'][0] ) && ! empty( $map_data['response_id'][0] ) ) ) ? count( $map_data['instrument_id'] ) : 0;

?>

<form id="tti-map-form">

	<div class="wrap tti-mapping-page">

		<h2>Mapping Rules</h2>

		<div class="field_wrapper">

			<div class="tti-mapping-fields">

			<?php
			if ( $map_count_loop ) {
				for ( $i = 0; $i <= $map_count_loop; $i++ ) {
					if ( ( isset( $map_data['instrument_id'][ $map_count ] ) && ! empty( $map_data['instrument_id'][ $map_count ] ) ) ||
						( isset( $map_data['response_id'][ $map_count ] ) && ! empty( $map_data['response_id'][ $map_count ] ) )
					) {
						?>
						<div class="tti-mapping-fields-inner">
							<div class="tti-mapping-fiel-sect">
								<input placeholder="Input Response Tag" value="<?php echo esc_attr( $map_data['response_id'][ $map_count ] ); ?>" class="response_id" id="response_id" name="response_id[]" />
							</div>

							<div class="tti-mapping-fiel-sect">
								<input placeholder="Input Instrument ID" value="<?php echo esc_attr( $map_data['instrument_id'][ $map_count ] ); ?>" class="instrument_id" id="instrument_id" name="instrument_id[]" />
							</div>

						<?php if ( 0 === $map_count ) { ?>
							<a href="javascript:void(0);" class="add_button" title="Add field"><img src="<?php echo esc_attr( esc_url_raw( MI_ADMIN_URL . 'images/add-icon.png', 'https' ) ); ?>"/></a>
						<?php } else { ?>
							<a href="javascript:void(0);" class="remove_button"><img src="<?php echo esc_attr( esc_url_raw( MI_ADMIN_URL . 'images/remove-icon.png', 'https' ) ); ?>"></a>
						<?php } $map_count++; ?>
							<div class="clear-fl-effect"></div>
						</div>
					<?php } ?>
				<?php } ?>

			<?php } else { ?> 
				<div class="tti-mapping-fields-inner">
					<div class="tti-mapping-fiel-sect">

					<?php if ( isset( $map_data['response_id'][0] ) ) { ?>
						<input placeholder="Input Response Tag" value="<?php echo esc_attr( $map_data['response_id'][0] ); ?>" class="response_id" id="response_id" name="response_id[]" />
					<?php } else { ?>
						<input placeholder="Input Response Tag" value="" class="response_id" id="response_id" name="response_id[]" />
					<?php } ?>

					</div>

					<div class="tti-mapping-fiel-sect">
						<?php if ( isset( $map_data['instrument_id'][0] ) ) { ?>
							<input placeholder="Input Instrument ID" value="<?php echo esc_attr( $map_data['instrument_id'][0] ); ?>" class="instrument_id" id="instrument_id" name="instrument_id[]" />
						<?php } else { ?>
							<input placeholder="Input Instrument ID" value="" class="instrument_id" id="instrument_id" name="instrument_id[]" />
						<?php } ?>

					</div>
						<a href="javascript:void(0);" class="add_button" title="Add field"><img src="<?php echo esc_attr( esc_url_raw( MI_ADMIN_URL . 'images/add-icon.png', 'https' ) ); ?>"/></a>
						<div class="clear-fl-effect"></div>
				</div>
			<?php } ?> 
			</div>
		</div>

		<button class="button button-primary button-large" id="tti-mapping-submit" type="button">Save Rules</button>
		<span id="loader_save_mapp">
			<img src="<?php echo esc_attr( esc_url_raw( MI_ADMIN_URL . 'images/loader.gif', 'https' ) ); ?>" alt="" width="20">
		</span>
	</div>
</form>
