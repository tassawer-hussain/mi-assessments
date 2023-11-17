<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/public/partials
 */

$print_optin = get_post_meta( $assess_id, 'print_report', true );

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php if ( 'yes' === strtolower( $print_optin ) ) { ?>

	<?php if ( 'yes' === $show_as_link ) { ?>
		<div class="tti-asses-history-sliderbutton">
			<a ><?php echo esc_html__( 'Click here to show/hide assessment history', 'tti-platform' ); ?></a>
		</div>
	<?php } ?>


	<div class="tti-asses-history-slider" data-show_link='<?php echo esc_attr( $show_as_link ); ?>' style="display:block;">

		<div class="tti-user-assess-history">
			<ol class="tti-activity-feed">

				<h3 style="margin-top:0px;"><?php esc_html_e( 'Assessment History', 'tti-platform' ); ?> </h3>

				<?php
				if ( isset( $data ) && ! empty( $data ) ) {

					$assess_title = get_the_title( $assess_id );


					foreach ( $data as $key => $value ) {
						$now             = new DateTime( $value->created_at );
						$assessment_date = $now->format( 'M j, Y' );

						$version = isset( $value->version ) ? $value->version : 1;

						$page_url = get_site_url();

						$assessment_download_url = esc_url( $page_url . '?assessment_id=' . $assess_id . '&version=' . $value->version, 'https' );
						$asses_download_icon_url = esc_url( MI_PUBLIC_URL . 'images/download.png', 'https' );

						?>
						<li class="feed-item">
							<time class="date" ><?php echo wp_kses_post( $assessment_date ); ?></time>
							<div class="assessment-details" id="user-assess-<?php echo esc_attr( $version ); ?>">

								<span><?php echo esc_html__( 'Title', 'tti-platform' ); ?> : <strong><?php echo esc_html( $assess_title ); ?></strong></span><br/>
								<span><?php echo esc_html__( 'Name', 'tti-platform' ); ?> : <strong><?php echo esc_html( $value->first_name . ' ' . $value->last_name ); ?></strong></span><br/>
								<span><?php echo esc_html__( 'Email', 'tti-platform' ); ?> : <strong><?php echo esc_html( $value->email ); ?></strong></span><br/>

								<?php if ( isset( $value->company ) && ! empty( $value->company ) ) { ?>
								<span><?php echo esc_html__( 'Company', 'tti-platform' ); ?>  : <strong><?php echo esc_html( $value->company ); ?></strong></span><br/>
								<?php } ?>

								<?php if ( isset( $value->gender ) && ! empty( $value->gender ) ) { ?>
								<span><?php echo esc_html__( 'Gender', 'tti-platform' ); ?> : <strong><?php echo esc_html( 'M' === $value->gender ? 'Male' : 'Female' ); ?></strong></span><br/>
								<?php } ?>

								<?php if ( isset( $value->position_job ) && ! empty( $value->position_job ) && 'none' !== $value->position_job ) { ?>
								<span><?php echo esc_html__( 'Position Job', 'tti-platform' ); ?> : <strong><?php echo esc_html( $value->position_job ); ?></strong></span><br/>
								<?php } ?>

								<?php if ( isset( $value->version ) && ! empty( $value->version ) ) { ?>
								<span><?php echo esc_html__( 'Version', 'tti-platform' ); ?> : <strong><?php echo esc_html( $value->version . '.0' ); ?></strong></span><br/>
								<?php } ?>

							</div>

							<span>
								<a assessment-id="<?php echo esc_attr( $assess_id ); ?>" class="download_pdf_user_history" href="<?php echo esc_attr( $assessment_download_url ); ?>" target="_blank">
									<img width="40" src="<?php echo esc_attr( $asses_download_icon_url ); ?>" alt="" />
									<span class="download-text"><?php echo esc_html__( 'Download Assessment', 'tti-platform' ); ?></span>
								</a>
							</span>
						</li>
						<?php
					}
				} else {
					esc_html_e( 'No Assessment History', 'tti-platform' );
				}
				?>
			</ol>
		</div>
	</div>
<?php } ?>
