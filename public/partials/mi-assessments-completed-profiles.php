<?php
/**
 * Display completed profiles of users in group.
 *
 * This is used to define complete profiles internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/public/partials
 */

/**
 * Fires before before main applicant dashboard
 *
 * @since   1.0.0
 */

do_action( 'ttisi_platform_before_cp_datatables' );
$counter = 0;
?>

<div class="tti_assessment_cp">
	<table id="tti_assessment_cp_table" class="display responsive nowrap" style="width:100%" >

		<thead>
			<th><?php esc_html_e( 'Respondent', 'tti-platform' ); ?></th>
			<th><?php esc_html_e( 'Email Address', 'tti-platform' ); ?></th>
			<th><?php esc_html_e( 'Organization Name', 'tti-platform' ); ?></th>
			<th><?php esc_html_e( 'Date Completed', 'tti-platform' ); ?></th>
			<th><?php esc_html_e( 'Report Type', 'tti-platform' ); ?></th>
			<th><?php esc_html_e( 'Download PDF', 'tti-platform' ); ?></th>
		</thead>

		<tbody>
			<?php
			if ( isset( $data ) && count( $data ) > 0 ) {

				foreach ( $data as $key => $value ) {

					$assess_id        = get_assessment_post_id_by_link_id( $value->link_id );
					$status_locked    = get_post_meta( $assess_id, 'status_locked', true );
					$report_api_check = get_post_meta( $assess_id, 'report_api_check', true );
					$status_locked    = ( 'true' === $status_locked ) ? 'close' : 'open';
					$pdf_img_url      = esc_url( MI_PUBLIC_URL . 'images/download.png', 'https' );

					if (
						'no' === strtolower( $report_api_check )
						&& null !== $value->selected_all_that_apply
						&& ! empty( $value->selected_all_that_apply )
					) {
						$pdf_download_url = esc_url( get_site_url() . '?report_type=quick_strength&user_id=' . $value->user_id . '&assess_id=' . $assess_id . '&tti_print_consolidation_report=1&version=' . $value->version, 'https' );
					} else {
						$pdf_download_url = esc_url( get_site_url() . '?assessment_id=' . $assess_id . '&version=' . $value->version . '&user_id=' . $value->user_id );
					}

					?>
					<tr>
						<td><?php echo esc_html( $value->first_name . ' ' . $value->last_name ); ?></td>
						<td><?php echo esc_html( $value->email ); ?></td>
						<td><?php echo esc_html( ( isset( $value->company ) && ! empty( $value->company ) ) ? $value->company : '-' ); ?></td>
						<td><?php format_the_date( $value->created_at ); ?></td>

						<td>
						<?php
						$report_type = get_the_title( $assess_id );
						if ( isset( $value->position_job ) && ! empty( $value->position_job ) ) {
							$report_type .= '<br /><br>';

							// Append the position job if it's not 'none'.
							if ( 'none' !== $value->position_job ) {
								$report_type .= $value->position_job;
							}
						}
						echo wp_kses_post( $report_type );
						?>
						</td>

						<td>
							<a
								target="_blank"
								id="tti_cp_download_btn"
								data-email="<?php echo esc_attr( $value->email ); ?>"
								data-assess="<?php echo esc_attr( $assess_id ); ?>"
								href="<?php echo esc_attr( $pdf_download_url ); ?>" >

								<img width="40px" src="<?php echo esc_attr( $pdf_img_url ); ?>" alt="" />
							</a>
						</td>
					</tr>
					<?php
				}
			}
			?>

		</tbody>
	</table>
</div>

<?php
/**
 * Fires before before main applicant dashboard
 *
 * @since   1.0.0
 */
do_action( 'ttisi_platform_after_cp_datatables' );
