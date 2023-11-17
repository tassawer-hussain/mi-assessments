<?php
/**
 * LDGR Group Users [wdm_group_users] shortcode tabs wrapper display template
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates/ldgr-group-users
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

// enqueue custom stylesheet.
wp_enqueue_style( 'mi-assessments-learndash-group-registration' );

?>
<form id='wdm_search_submit' method='post'>
	<input name="wdm_group_id" type="hidden" value="<?php echo esc_attr( $group_id ); ?>">
</form>

<div class="ldgr-lightbox" id="ldgr-edit-group-popup">
	<div class="ldgr-popup">
		<i class="ldgr-icon-Close"></i>
		<h3 class="ldgr-popup-title">
			<?php
			echo sprintf(
				// translators: Group label.
				esc_html__( 'Edit %s details', 'wdm_ld_group' ),
				\LearnDash_Custom_Label::label_to_lower( 'group' )
			);
			?>
		</h3>
		<div class="ldgr-popup-content">
			<div class="ldgr-edit-gn">
				<label>
					<?php
					echo esc_html(
						sprintf(
							// translators: Group label.
							__( '%s Name', 'wdm_ld_group' ),
							\LearnDash_Custom_Label::get_label( 'group' )
						)
					);
					?>
				</label>
				<input type="text" value="<?php echo esc_html( get_the_title( $group_id ) ); ?>" name="ldgr-edit-group-name" data-group_id="<?php echo esc_html( $group_id ); ?>">
			</div>
			<div class="ldgr-edit-gicon">
				<label>
					<?php
					echo sprintf(
						// translators: Group label.
						esc_html__( '%s Image', 'wdm_ld_group' ),
						\LearnDash_Custom_Label::get_label( 'group' )
					);
					?>
				</label>
				<div>
					<span class="ldgr-curr-icon">
						<!-- <i class="ldgr-icon-Language"></i> -->
						<?php $instance->display_group_image( $group_id, 50 ); ?>
					</span>
					<input type="hidden" id="ldgr-edit-group-image" name="ldgr-edit-group-image" value="" />
					<span class="ldgr-ch-icon ldgr-color"><i class="ldgr-icon-Edit"></i><?php esc_html_e( 'Change image', 'wdm_ld_group' ); ?></span>
					<span class="ldgr-rm-icon"><i class="ldgr-icon-Trash"></i><?php esc_html_e( 'Remove image', 'wdm_ld_group' ); ?></span>
				</div>
				<p class="ldgr-note"><?php esc_html_e( 'Image size should be 200x200px. File should not exceed 1mb', 'wdm_ld_group' ); ?></p>
			</div>
			<div class="ldgr-eg-actions">
				<span class="ldgr-btn edit-group-cancel"><?php esc_html_e( 'Cancel', 'wdm_ld_group' ); ?></span>
				<span class="ldgr-btn ldgr-bg-color solid update-group-details" id="ldgr-update-group-details"><?php esc_html_e( 'Update', 'wdm_ld_group' ); ?></span>
			</div>
			<form method="post" class="update_group_details_group_form">
				<input type="hidden" name="wdm_group_id" value="<?php echo esc_html( $group_id ); ?>">
			</form>
		</div>
	</div>
</div>

<div class="ldgr-lightbox" id="ldgr-enroll-users-popup">
	<div class="ldgr-popup">
		<i class="ldgr-icon-Close"></i>
		<h3 class="ldgr-popup-title"><?php esc_html_e( 'Enroll New Users', 'wdm_ld_group' ); ?></h3>
		<div class="ldgr-popup-content">
			<div class="ldgr-enroll-user-content">
				<ul class="ldgr-tabs">
					<li class="ldgr-add-users current" data-name="ldgr-add-users"><?php esc_html_e( 'Add Users', 'wdm_ld_group' ); ?></li>
					<li class="ldgr-upload-csv" data-name="ldgr-upload-csv"><?php esc_html_e( 'Upload via CSV', 'wdm_ld_group' ); ?></li>
				</ul>
				<div class="ldgr-tabs-content">
					<div class="ldgr-add-users-wrap current" data-name="ldgr-add-users">
						<form id='wdm_add_user_fields' method='post'>
							<input type='hidden' name='wdm_add_user_check' value='1'>
							<input type='hidden' name='wdm_group_id' value='<?php echo esc_attr( $group_id ); ?>'>
							<div class="ldgr-add-users">
								<div class="ldgr-add-user">
									<div class="ldgr-field">
										<label><?php esc_html_e( 'first name', 'wdm_ld_group' ); ?></label>
										<input type="text" class="ldgr-textbox" name='wdm_members_fname[]' />
										<span class="ldgr-field-error"></span>
									</div>
									<div class="ldgr-field">
										<label><?php esc_html_e( 'last name', 'wdm_ld_group' ); ?></label>
										<input type="text" class="ldgr-textbox" name='wdm_members_lname[]'>
										<span class="ldgr-field-error"></span>
									</div>
									<div class="ldgr-field">
										<label><?php esc_html_e( 'email', 'wdm_ld_group' ); ?></label>
										<input type="text" class="ldgr-textbox" name='wdm_members_email[]' />
										<span class="ldgr-field-error"></span>
									</div>
									<?php
									if ( ! empty( $dynamic_fields ) && is_array( $dynamic_fields ) ) {
										$dynamic_field_class = new \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Dynamic_Fields();
										foreach ( $dynamic_fields as $key => $value ) {
											$html = $dynamic_field_class->create_dynamic_field( $value );
											echo $html;
										}
									}
									?>
									<div class="ldgr-field remove-user">
										<i class="ldgr-icon-Trash"></i>
										<span class="ldgr-rm-usr"><?php esc_html_e( 'Remove User', 'wdm_ld_group' ); ?></span>
									</div>
								</div>
							</div>
							<div class="ldgr-add-more-users">
								<span class="ldgr-plus"><i class="ldgr-icon-Add"></i></span>
								<span class="ldgr-color ldgr-amu"><?php esc_html_e( 'Add more users', 'wdm_ld_group' ); ?></span>
							</div>
							<div class="ldgr-eg-actions">
								<span class="ldgr-btn add-usr-cancel"><?php esc_html_e( 'Cancel', 'wdm_ld_group' ); ?></span>
								<span id="ldgr-add-users-submit" class="ldgr-btn ldgr-bg-color solid"><?php esc_html_e( 'Submit', 'wdm_ld_group' ); ?></span>
								<?php wp_nonce_field( 'ldgr_enroll_users', 'ldgr_enroll_users_nonce' ); ?>
							</div>
						</form>
					</div>
					<div class="ldgr-upload-csv" data-name="ldgr-upload-csv">
						<form enctype="multipart/form-data" name="import-upload-form" id="import-upload-form" method="post" class="wp-upload-form" >
							<div class="ldgr-upload-wrap">
								<div class="ldgr-uploader">
									<span class="ldgr-info"><?php esc_html_e( 'Drag and drop the CSV file here OR', 'wdm_ld_group' ); ?></span>
									<span for="uploadcsv" class="ldgr-btn ldgr-upload-btn"><?php esc_html_e( 'Choose File', 'wdm_ld_group' ); ?></span>
									<input type="file" id="uploadcsv" name="uploadcsv" size="25" />
									<div id="ldgr-upload-file-info">
										<div id="ldgr-upload-file-name"><?php esc_html_e( 'File Name:', 'wdm_ld_group' ); ?>
											<span></span>
										</div>
										<div id="ldgr-upload-file-size"><?php esc_html_e( 'File Size:', 'wdm_ld_group' ); ?>
											<span></span>
										</div>
									</div>
								</div>
								<div class="ldgr-dlcsv">
									<a download href="<?php echo esc_url( plugins_url( 'modules/sample/demo.csv', WDM_LDGR_PLUGIN_FILE ) ); ?>" title="<?php echo esc_html__( 'Download sample csv', 'wdm_ld_group' ); ?>">
										<i class="ldgr-icon-Download"></i>
									</a>
									<span class="ldgr-color dlcsv-txt">
										<?php esc_html_e( 'Download Sample CSV', 'wdm_ld_group' ); ?>
									</span>
								</div>
							</div>
							<div class="ldgr-eg-actions">
								<span class="ldgr-btn upload-csv-cancel"><?php esc_html_e( 'Cancel', 'wdm_ld_group' ); ?></span>
								<span class="ldgr-btn ldgr-bg-color solid ldgr-upload-csv-btn"><?php esc_html_e( 'Upload', 'wdm_ld_group' ); ?></span>
							</div>
							<input type='hidden' name='wdm_upload_check' value='1'>
							<input type="hidden" name="wdm_group_id" value="<?php echo esc_attr( $group_id ); ?>">
							<?php wp_nonce_field( 'wdm_ldgr_csv_upload_enroll', 'wdm_ldgr_csv_upload_enroll_field' ); ?>
							<div class="blocked hide">
								<span class="dashicons dashicons-update spin"></span>
								<div class="wdm-progress-container">
									<div class="wdm-progress-bar"></div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="ldgr-group-single">
	<div class="ldgr-top-banner" style="<?php echo ( ! empty( $colors['banner'] ) ) ? 'background-color: ' . esc_html( $colors['banner'] ) : ''; ?>">
		<div class="ldgrs-left">
			<div class="ldgrs-title-wrap">
				<div>
					<!-- <i class="ldgr-icon-Language"></i> -->
					<?php $instance->display_group_image( $group_id, 150 ); ?>
					<div class="ldgr-gr-title-info">
						<h1><?php echo esc_html( get_the_title( $group_id ) ); ?></h1>
						<span class="ldgr-u-left">
							<?php if ( ! $is_unlimited ) : ?>
								<?php
								echo esc_html(
									// translators: group user count, total group seats.
									sprintf( __( '%1$d / %2$d user registrations left', 'wdm_ld_group' ), $grp_limit_count, $total_group_limit )
								);
								?>
							<?php endif; ?>
						</span>
						<div>
						<?php
						// echo esc_html(
						// sprintf(
						// * translators: Total no of seats. */
						// __( '&nbsp; &nbsp;Related product ID : %s', 'wdm_ld_group' ),
						// ldgr_check_related_group_product( $group_id ),
						// )
						// );
						?>
						</div>

						<?php
						$ldgr_group_courses = get_option( 'ldgr_group_courses' );
						if ( 'on' === $ldgr_group_courses ) {

							$output  = '<div class="misg-course-wrapper">';
							$output .= '<p>Included Courses</p>';
							$output .= '<ul>';

							foreach ( $group_courses as $course_id ) {
								$output .= '<li>';
								$output .= '<h2>';
								$output .= '<span class="gr-title ttisi-ldgr-group-course-name" title="' . esc_attr( get_the_title( $course_id ) ) . '">';
								$output .= esc_html( get_the_title( $course_id ) );
								$output .= '</span>';
								$output .= '</h2>';
								$output .= '</li>';
							}

							$output .= '</ul>';
							$output .= '</div>';

							echo wp_kses_post( $output );
						}
						?>

						<div class="sub-group-of">
							<?php if ( $is_sub_group ) : ?>
								<em>
									<?php
									echo esc_html(
										// translators: sub group label, parent group title.
										sprintf(
											__( '%1$s of %2$s', 'wdm_ld_group' ),
											\LearnDash_Custom_Label::get_label( 'subgroup' ),
											get_the_title( $parent_group_id )
										)
									);
									?>
								</em>
							<?php endif; ?>
						</div>
					</div>
				</div>

			</div>
		</div>
		<div class="ldgr-right-button-container" style="text-align:end;">

			<div class="ldgrs-right ldgr-back-to-groups">
				<div class="ldgrs-edit-wrap">
					<div>
						<?php if ( ! $single_group || $is_sub_group ) : ?>
						<form method="post">
							<input name="wdm_group_id" value="" type="hidden">
							<button class="ldgr-btn th-set-it" type="submit">
								<?php
								echo sprintf(
									// translators: Group label.
									esc_html__( 'Back to %s', 'wdm_ld_group' ),
									\LearnDash_Custom_Label::get_label( 'groups' )
								);
								?>
							</button>
						</form>
						<?php endif; ?>

						<span class="cnsg-btn tti-stack-btn">
							<?php
							echo sprintf(
								// translators: Sub Group label.
								esc_html__( 'Create New %s', 'wdm_ld_group' ),
								\LearnDash_Custom_Label::get_label( 'subgroup' )
							);
							?>
						</span>

						<?php if ( $grp_limit_count > 0 ) : ?>
							<span class="enroll-new-user tti-stack-btn">
								<?php esc_html_e( 'Enroll New User', 'wdm_ld_group' ); ?>
							</span>
						<?php endif; ?>

						<div class="ldgr-edit-group ldgr-edit-groups-css ldgr-color">
							<i class="ldgr-icon-Edit"></i>
							<?php
							echo sprintf(
								// translators: Group label.
								esc_html__( 'Edit %s details', 'wdm_ld_group' ),
								\LearnDash_Custom_Label::label_to_lower( 'group' )
							);
							?>
						</div>

					</div>
				</div>
			</div>

		</div>
	</div>
	<?php if ( ! empty( $group_courses ) ) : ?>
	<div class="ldgr-group-courses">
		<h3>
			<?php
			echo sprintf(
				// translators: Course label.
				esc_html__( '%s Included', 'wdm_ld_group' ),
				\LearnDash_Custom_Label::get_label( 'courses' )
			);
			?>
		</h3>
		<div class="ldgr-group-courses-items">
			<?php foreach ( $group_courses as $course_id ) : ?>
				<a href="<?php echo esc_url( get_the_permalink( $course_id ) ); ?>" target="blank">
				<div class="ldgr-group-courses-item">
					<?php
					if ( get_the_post_thumbnail_url( $course_id ) ) {
						$src = esc_url( get_the_post_thumbnail_url( $course_id ) );
					} else {
						$def_course_image = get_option( 'ldgr_default_course_image' );
						if ( $image = wp_get_attachment_image_src( $def_course_image ) ) {
							$src = esc_url( $image[0] );
						} else {
							$src = esc_url( plugins_url( 'assets/images/no_image.png', WDM_LDGR_PLUGIN_FILE ) );
						}
					}
					?>
					<img style="width: 160px; height: 80px; object-fit: contain;" src="<?php echo $src; ?>">
					<span title="<?php echo esc_attr( get_the_title( $course_id ) ); ?>"><?php echo esc_html( mb_strimwidth( get_the_title( $course_id ), 0, apply_filters( 'ldgr_course_character_truncate_limit', 50 ), '...' ) ); ?></span>
				</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</div>
<div id = "wdm_groups_tab" class="wdm-tabs-wrapper">
		<div class="wdm-tabs-inner-links">

			<?php
			if ( ! $need_to_restrict && ( $grp_limit_count > 0 || $is_unlimited ) ) :
				;
				?>
					<span class="enroll-new-user ldgr-btn ldgr-bg-color ldgr-mobile"><?php esc_html_e( 'Enroll New User', 'wdm_ld_group' ); ?></span>
				<?php else : ?>
					<?php
					$group_meta = (array) get_post_meta( $group_id );
					if ( isset( $group_meta[ 'wdm_group_reg_order_id_' . $group_id ] ) ) {
						$group_order_id = $group_meta[ 'wdm_group_reg_order_id_' . $group_id ];
						$group_order    = wc_get_order( $group_order_id[0] );
						foreach ( $group_order->get_items() as $item_id => $item ) {
							if ( $item['order_id'] == (int) $group_order_id[0] ) {
								?>
							<a href=<?php echo get_permalink( esc_attr( $item['product_id'] ) ); ?>>
								<span class="add-new-seats ldgr-btn ldgr-bg-color ldgr-mobile"><?php esc_html_e( 'Add more seats', 'wdm_ld_group' ); ?></span>
							<a> 
								<?php
							}
						}
					}
					?>
				<?php endif; ?>




			<ul class="tabs">
				<?php foreach ( $tab_headers as $header ) : ?>
					<?php
					if ( $instance->not_required_tab( $header ) ) {
						continue; }
					?>
					<li class="tab-link <?php echo ( current( $tab_headers ) === $header ) ? 'current' : ''; ?>" data-tab="tab-<?php echo esc_html( $header['id'] ); ?>">
						<a href="#" class="wdm-for-desktop">
							<?php
							/**
							 * Dyanamic filters for groups dashboard tab headers.
							 *
							 * @since 4.2.0
							 */
								echo esc_html(
									apply_filters(
										$header['slug'],
										$header['title']
									)
								);
							?>
						</a>
						<a href="#" class="wdm-for-mobile">
							<img src="<?php echo esc_url( $header['icon'] ); ?>">
							<?php
							/**
							 * Dyanamic filters for groups dashboard tab headers.
							 *
							 * @since 4.2.0
							 */
								echo esc_html(
									apply_filters(
										$header['slug'],
										$header['title']
									)
								);
							?>
						</a>
					</li>
				<?php endforeach; ?>

				<?php
				if ( ! $need_to_restrict && ( $grp_limit_count > 0 || $is_unlimited ) ) :
					;
					?>
					<span class="enroll-new-user ldgr-btn ldgr-bg-color ldgr-desktop"><?php esc_html_e( 'Enroll New User', 'wdm_ld_group' ); ?></span>
				<?php else : ?>
					<?php
					$group_meta = (array) get_post_meta( $group_id );
					if ( isset( $group_meta[ 'wdm_group_reg_order_id_' . $group_id ] ) ) {
						$group_order_id = $group_meta[ 'wdm_group_reg_order_id_' . $group_id ];
						$group_order    = wc_get_order( $group_order_id[0] );
						foreach ( $group_order->get_items() as $item_id => $item ) {
							if ( $item['order_id'] == (int) $group_order_id[0] ) {
								?>
							<a href=<?php echo get_permalink( esc_attr( $item['product_id'] ) ); ?>>
								<span class="add-new-seats ldgr-btn ldgr-bg-color ldgr-desktop"><?php esc_html_e( 'Add more seats', 'wdm_ld_group' ); ?></span>
							<a> 
								<?php
							}
						}
					}
					?>
				<?php endif; ?>

			</ul>
		</div>

		<?php foreach ( $tab_contents as $content ) : ?>
			<?php
				/**
				 * Before groups dashboard tab.
				 *
				 * @param array $content    List of data to be used to display current tab.
				 *
				 * @since 4.2.0
				 */
				do_action( 'ldgr_action_before_group_tab_' . $content['id'], $content );
			?>
			<?php include $content['template']; ?>
			<?php
				/**
				 * After groups dashboard tab.
				 *
				 * @param array $content    List of data to be used to display current tab.
				 *
				 * @since 4.2.0
				 */
				do_action( 'ldgr_action_after_group_tab_' . $content['id'], $content );
			?>
		<?php endforeach; ?>

</div> <!-- End of tabs-wrapper -->
