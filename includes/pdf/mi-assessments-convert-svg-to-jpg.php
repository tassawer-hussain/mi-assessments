<?php
/**
 * File to convert SVG to JPG
 * Contains script to convert SVG to JPG
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/includes
 */

?>

<html>
	<head>
		<title>TTI Insights</title>

		<?php // phpcs:ignore ?>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

		<style>
			body {
				background: #1b3a63;
				text-align: center;
				overflow: hidden; /* Hide scrollbars */
			}

			.loader {
				width: 410px;
				height: 50px;
				line-height: 50px;
				text-align: center;
				position: absolute;
				font-size: 20px;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				-webkit-transform: translate(-50%, -50%);
				font-family: helvetica, arial, sans-serif;
				text-transform: uppercase;
				font-weight: 900;
				color: #fff;
				letter-spacing: 0.2em;
			}
			.loader::before, .loader::after {
				content: "";
				display: block;
				width: 12px;
				height: 12px;
				background: #fff;
				position: absolute;
				animation: load .7s infinite alternate ease-in-out;
				-webkit-animation: load .7s infinite alternate ease-in-out;
			}
			.loader::before {
				top: 0;
			}
			.loader::after {
				bottom: 0;
			}

			@-webkit-keyframes load {
				0% {
					left: 0;
					height: 30px;
					width: 15px;
				}
				50% {
					height: 8px;
					width: 40px;
				}
				100% {
					left: 3px;
					height: 30px;
					width: 15px;
				}
			}

			@keyframes load {
				0% {
					left: 0;
					height: 30px;
					width: 15px;
				}
				50% {
					height: 8px;
					width: 40px;
				}
				100% {
					left: 425px;
					height: 30px;
					width: 15px;
				}
			}
		</style>
	</head>

	<body style="background-color:#E6E6FA;">

		<?php

			// filter Global $_GET variable.
			$_get_data = filter_input_array( INPUT_GET );

			$svg_file_link = isset( $_get_data['svg_url'] ) ? $_get_data['svg_url'] : '';
			$key_name      = isset( $_get_data['key_name'] ) ? $_get_data['key_name'] : '';
			$assess_id     = isset( $_get_data['assess_id'] ) ? $_get_data['assess_id'] : '';
			$report_type   = isset( $_get_data['report_type'] ) ? $_get_data['report_type'] : '';
			$user_id       = isset( $_get_data['user_id'] ) ? $_get_data['user_id'] : '';

			$svg_file = file_get_contents( $svg_file_link ); // phpcs:ignore

			$position     = strpos( $svg_file, '<svg' );
			$svg_file_new = substr( $svg_file, $position );
		?>

		<div class="loader">
			<span style="position: relative;left: 14px;">...Creating PDF Report...</span>
		</div>

		<textarea id="t" rows="8" cols="70" style="display:none;" value="<?php echo esc_attr( $svg_file ); ?>"></textarea>

		<div style="visibility: hidden;" id="d"></div><br/>
		<input style="visibility: hidden;" id="w" type="number" max="9999"></input>
		<input style="visibility: hidden;" id="h" type="number" max="9999"></input>

		<canvas style="visibility: hidden;" id="c"></canvas>

		<script>
			/* SVG to PNG (c) 2017 CY Wong / myByways.com */
			var text = document.getElementById('t');
			text.wrap = 'off';

			var svg = null;
			var width = document.getElementById('w');
			var height = document.getElementById('h'); 

			var div = document.getElementById('d');
			div.innerHTML= text.value;
			width.value = svg.getBoundingClientRect().width;
			height.value = svg.getBoundingClientRect().height;

			svg = div.querySelector('svg');
			svg.setAttribute('width', width.value);
			svg.setAttribute('height', height.value);

			var canvas = document.getElementById('c');
			canvas.width = width.value;
			canvas.height = height.value;

			var data = new XMLSerializer().serializeToString(svg);
			var win = window.URL || window.webkitURL || window;
			var img = new Image();
			var blob = new Blob([data], { type: 'image/svg+xml' });
			var url = win.createObjectURL(blob);

			img.onload = function () {
				canvas.getContext('2d').drawImage(img, 0, 0);
				win.revokeObjectURL(url);
				var uri = canvas.toDataURL('image/png');

				var a = document.createElement('a');
				document.body.appendChild(a);
				a.style = 'display: none';
				a.href = uri
				a.download = (svg.id || svg.getAttribute('name') || svg.getAttribute('aria-label') || 'wheel_chart') + '.png';

				loads_the_img(uri, url);
				window.URL.revokeObjectURL(uri);
			};

			img.src = url;

			function loads_the_img(uri,url) {
				$.ajax({
					type: "POST",
					url: 'mi-assessments-convert-svg-ajax-call.php',
					data: {
						uri: uri,
						url: url,
						keyname: <?php echo esc_attr( $key_name ); ?>
					},
					success: function(data){
						$('.loader span').text('...Downloading PDF...');

						<?php
						if ( $user_id ) {
							$location_arg = '?report_type=' . $report_type . '&user_id=' . $user_id . '&assess_id=' . $assess_id . '&tti_print_consolidation_report=1&keyname=' . $key_name;
						} else {
							$location_arg = '?report_type=' . $report_type . '&assess_id=' . $assess_id . '&tti_print_consolidation_report=1&keyname=' . $key_name;
						}
						?>

						window.location= window.location.origin + <?php esc_attr( $location_arg ); ?>;

						setInterval( function() {
							$('.loader span').text('...Download Complete...');
						}, 3000 );

						setInterval( function() {
							window.close();
						}, 5000 );
					}
				});
			}
		</script>

	</body>
</html>

