<?php
/**
 * Transcoder actions.
 *
 * @since      1.0.7
 *
 * @package    Transcoder
 * @subpackage Transcoder/Actions
 */

/**
 * This filter has been commented because it was conflicting  with the
 * set-custom-thumbnail addon.
 */

if ( ! function_exists( 'rtt_video_editor_title' ) ) {
	/**
	 * Add the video thumbnail tab on video edit page.
	 *
	 * @since   1.0.0
	 */
	function rtt_video_editor_title() {
		global $rtmedia_query;
		if ( isset( $rtmedia_query->media[0]->media_type ) && 'video' === $rtmedia_query->media[0]->media_type ) {
			$flag            = false;
			$media_id        = $rtmedia_query->media[0]->media_id;
			$thumbnail_array = get_post_meta( $media_id, 'rtmedia_media_thumbnails', true );
			if ( ! is_array( $thumbnail_array ) ) {
				$thumbnail_array = get_post_meta( $media_id, '_rt_media_thumbnails', true );
			}
			if ( is_array( $thumbnail_array ) ) {
				$flag = true;
			} else {
				global $rtmedia_media;
				$curr_cover_art = $rtmedia_media->cover_art;
				if ( ! empty( $curr_cover_art ) ) {
					$rtmedia_video_thumbs = get_rtmedia_meta( $rtmedia_query->media[0]->media_id, 'rtmedia-thumbnail-ids' );
					if ( is_array( $rtmedia_video_thumbs ) ) {
						$flag = true;
					}
				}
			}
			if ( $flag ) {
				echo '<li><a href="#panel2"><i class="dashicons dashicons-format-image rtmicon"></i>' . esc_html__( 'Video Thumbnail', 'transcoder' ) . '</a></li>';
			}
		}
	}
}

add_action( 'rtmedia_add_edit_tab_title', 'rtt_video_editor_title', 1000 );

if ( ! function_exists( 'rtt_rtmedia_vedio_editor_content' ) ) {
	/**
	 * Display the HTML to set the thumbnail for video.
	 *
	 * @since   1.0.0
	 */
	function rtt_rtmedia_vedio_editor_content() {
		global $rtmedia_query;
		if ( isset( $rtmedia_query->media ) && is_array( $rtmedia_query->media ) && isset( $rtmedia_query->media[0]->media_type ) && 'video' === $rtmedia_query->media[0]->media_type ) {
			$media_id                        = $rtmedia_query->media[0]->media_id;
			$rtmedia_transcoded_video_thumbs = get_post_meta( $rtmedia_query->media[0]->media_id, '_rt_media_thumbnails', true );

			if ( ! is_array( $rtmedia_transcoded_video_thumbs ) ) {
				$rtmedia_transcoded_video_thumbs = get_post_meta( $media_id, 'rtmedia_media_thumbnails', true );
			}
			echo '<div class="content" id="panel2">';
			if ( is_array( $rtmedia_transcoded_video_thumbs ) ) {
				?>
				<div class="rtmedia-change-cover-arts">
					<p><?php esc_html_e( 'Video Thumbnail:', 'transcoder' ); ?></p>
					<ul>
						<?php
						/* for WordPress backward compatibility */
						if ( function_exists( 'wp_get_upload_dir' ) ) {
							$uploads = wp_get_upload_dir();
						} else {
							$uploads = wp_upload_dir();
						}
						$media_id = $rtmedia_query->media[0]->media_id;
						foreach ( $rtmedia_transcoded_video_thumbs as $key => $thumbnail_src ) {
							$wp_video_thumbnail = get_post_meta( $media_id, '_rt_media_video_thumbnail', true );

							if ( 0 === strpos( $thumbnail_src, $uploads['baseurl'] ) ) {
								$thumbnail_src = str_replace( $uploads['baseurl'], '', $thumbnail_src );
							}

							if ( empty( $wp_video_thumbnail ) ) {
								$wp_video_thumbnail = $rtmedia_query->media[0]->cover_art;
								$wp_video_thumbnail = str_replace( $uploads['baseurl'], '', $wp_video_thumbnail );
							}

							$checked          = false;
							$thumbnail_src_og = $thumbnail_src;
							if ( $wp_video_thumbnail === $thumbnail_src ) {
								$checked = 'checked=checked';
							}

							$file_url = $thumbnail_src;

							if ( 0 === strpos( $file_url, $uploads['baseurl'] ) ) {
								$thumbnail_src = $file_url;
							} else {
								$thumbnail_src = $uploads['baseurl'] . '/' . $file_url;
							}
							$thumbnail_src = apply_filters( 'transcoded_file_url', $thumbnail_src, $media_id );
							?>
							<li<?php echo $checked ? ' class="selected"' : ''; ?>
								style="width: 150px;display: inline-block;">
								<label for="rtmedia-upload-select-thumbnail-<?php echo intval( sanitize_text_field( $key ) ) + 1; ?>" class="alignleft">
									<input type="radio"<?php echo esc_attr( $checked ); ?>
										id="rtmedia-upload-select-thumbnail-<?php echo intval( sanitize_text_field( $key ) ) + 1; ?>"
										value="<?php echo esc_attr( $thumbnail_src_og ); ?>"
										name="rtmedia-thumbnail"/>
									<img src="<?php echo esc_url( $thumbnail_src ); ?>" style="max-height: 120px;max-width: 120px"/>
								</label>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
				<?php
			} else { // check for array of thumbs stored as attachement ids.
				global $rtmedia_media;
				$curr_cover_art = $rtmedia_media->cover_art;
				if ( ! empty( $curr_cover_art ) ) {
					$rtmedia_video_thumbs = get_rtmedia_meta( $rtmedia_query->media[0]->media_id, 'rtmedia-thumbnail-ids' );
					if ( is_array( $rtmedia_video_thumbs ) ) {
						?>
						<div class="rtmedia-change-cover-arts">
							<p><?php esc_html_e( 'Video Thumbnail:', 'transcoder' ); ?></p>
							<ul>
								<?php
								foreach ( $rtmedia_video_thumbs as $key => $attachment_id ) {
									$thumbnail_src = wp_get_attachment_url( $attachment_id );
									?>
									<li<?php echo checked( $attachment_id, $curr_cover_art, false ) ? ' class="selected"' : ''; ?>
										style="width: 150px;display: inline-block;">
										<label for="rtmedia-upload-select-thumbnail-<?php echo intval( sanitize_text_field( $key ) ) + 1; ?>" class="alignleft">
											<input type="radio"<?php checked( $attachment_id, $curr_cover_art ); ?>
												id="rtmedia-upload-select-thumbnail-<?php echo intval( sanitize_text_field( $key ) ) + 1; ?>"
												value="<?php echo esc_attr( $attachment_id ); ?>"
												name="rtmedia-thumbnail"/>
											<img src="<?php echo esc_url( $thumbnail_src ); ?>" style="max-height: 120px;max-width: 120px"/>
										</label>
									</li>
									<?php
								}
								?>
							</ul>
						</div>

						<?php
					}
				}
			}
			echo '</div>';
		}
	}
}

add_action( 'rtmedia_add_edit_tab_content', 'rtt_rtmedia_vedio_editor_content', 1000 );

if ( ! function_exists( 'rtt_set_video_thumbnail' ) ) {

	/**
	 * Set the video thumbnail
	 *
	 * @since   1.0.0
	 *
	 * @param number $id rtMedia activity ID.
	 */
	function rtt_set_video_thumbnail( $id ) {
		$media_type    = rtmedia_type( $id );
		$attachment_id = rtmedia_media_id( $id );      // Get the wp attachment ID.
		$thumbnail     = transcoder_filter_input( INPUT_POST, 'rtmedia-thumbnail', FILTER_SANITIZE_URL );
		if ( 'video' === $media_type && ! empty( $thumbnail ) ) {

			if ( ! is_numeric( $thumbnail ) ) {
				$file_url = $thumbnail;
				/* for WordPress backward compatibility */
				if ( function_exists( 'wp_get_upload_dir' ) ) {
					$uploads = wp_get_upload_dir();
				} else {
					$uploads = wp_upload_dir();
				}
				if ( 0 === strpos( $file_url, $uploads['baseurl'] ) ) {
					$final_file_url = $file_url;
				} else {
					$final_file_url = $uploads['baseurl'] . '/' . $file_url;
				}

				$final_file_url = apply_filters( 'transcoded_file_url', $final_file_url, $attachment_id );

				update_post_meta( $attachment_id, '_rt_media_video_thumbnail', $thumbnail );
			}

			$model = new RTMediaModel();
			$model->update( array( 'cover_art' => $final_file_url ), array( 'id' => intval( $id ) ) );
			rtt_update_activity_after_thumb_set( $id );

		}
	}
}

add_action( 'rtmedia_after_update_media', 'rtt_set_video_thumbnail', 12 );

/**
 * Set the cover art/video thumbnail for the videos which are not uploaded from the rtMedia activity
 *
 * @since 1.0.7
 * @param string $thumb_url     Video thumbnail URL.
 * @param int    $attachment_id Attachment ID of the media/video for which thumbnail has to be set.
 */
function rtt_update_wp_media_thumbnail( $thumb_url, $attachment_id ) {
	if ( class_exists( 'RTMediaModel' ) ) {
		$model = new RTMediaModel();
		$media = $model->get( array( 'media_id' => $attachment_id ) );

		if ( ! empty( $media ) && ! empty( $media[0] ) ) {
			$attachment_id = $media[0]->media_id;
			$media_type    = $media[0]->media_type;
			$cover_art     = $media[0]->cover_art;

			if ( 'video' === $media_type && empty( $cover_art ) && ! empty( $thumb_url ) ) {
				$model->update( array( 'cover_art' => $thumb_url ), array( 'media_id' => $attachment_id ) );
			}
		}
	}
}

add_action( 'transcoded_thumb_added', 'rtt_update_wp_media_thumbnail', 10, 2 );

/**
 * Add a field for the transcoded URL to the media attachment edit screen.
 *
 * @param array  $form_fields An array of attachment form fields.
 * @param object $post The attachment post object.
 * @return array The modified array of attachment form fields.
 */
function add_transcoded_url_field( $form_fields, $post ) {
	$transcoded_url = get_post_meta( $post->ID, '_rt_transcoded_url', true );

	// Check if adaptive bitrate streaming is enabled.
	$adaptive_bitrate_enabled = get_option( 'rtt_adaptive_bitrate_streaming', false );

	// Add the transcoded URL field.
	$form_fields['transcoded_url'] = array(
		'label' => __( 'Transcoded MPD URL', 'transcoder' ),
		'input' => 'html',
		'html'  => '<input type="text" name="attachments[' . $post->ID . '][transcoded_url]" id="attachments-' . $post->ID . '-transcoded_url" value="' . esc_url( $transcoded_url ) . '" ' . disabled( ! $adaptive_bitrate_enabled, true, false ) . '>',
		'value' => esc_url( $transcoded_url ),
		'helps' => __( 'Enter or edit the URL of the transcoded .mpd file stored on Amazon S3.', 'transcoder' ),
	);

	// Add a note if adaptive bitrate streaming is disabled.
	if ( ! $adaptive_bitrate_enabled ) {
		$form_fields['transcoded_url']['helps'] = __( 'This feature is available only when adaptive bitrate streaming is enabled.', 'transcoder' );
	}

	return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'add_transcoded_url_field', 10, 2 );

/**
 * Save the transcoded URL field when the attachment is saved.
 *
 * @param array $post The post data for the attachment.
 * @param array $attachment The attachment data.
 * @return array The post data for the attachment.
 */
function save_transcoded_url_field( $post, $attachment ) {
	// Check if adaptive bitrate streaming is enabled.
	$adaptive_bitrate_enabled = get_option( 'rtt_adaptive_bitrate_streaming', false );
	if ( ! $adaptive_bitrate_enabled ) {
		return $post;
	}

	if ( isset( $attachment['transcoded_url'] ) ) {
		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post['ID'] ) ) {
			return $post;
		}
		// Update the post meta with the new value.
		update_post_meta( $post['ID'], '_rt_transcoded_url', esc_url_raw( $attachment['transcoded_url'] ) );
	}

	return $post;
}

add_filter( 'attachment_fields_to_save', 'save_transcoded_url_field', 10, 2 );

/**
 * Register the transcoded URL meta field.
 */
function register_rt_transcoded_url_meta() {
	register_post_meta(
		'attachment',
		'_rt_transcoded_url',
		array(
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) 
	);
}
add_action( 'init', 'register_rt_transcoded_url_meta' );

/**
 * Add a watermark to the media using FFmpeg.
 * 
 * @param array $upload The uploaded file data.
 * 
 * @return array The modified uploaded file data.
 */
function add_text_watermark_to_media( $upload ) {

	// Check if the uploaded file is a video or image.
	$file_type        = wp_check_filetype( $upload['file'] );
	$video_extensions = array( 'mp4', 'mov', 'avi', 'mkv' );
	$image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'bmp' );

	if ( in_array( $file_type['ext'], $video_extensions ) ) {
		$is_video = true;
	} elseif ( in_array( $file_type['ext'], $image_extensions ) ) {
		$is_video = false;
	} else {
		return $upload;
	}

	// Check if the watermark is enabled.
	$watermark_enabled = get_option( 'rtt_watermark', false );
	if ( ! $watermark_enabled ) {
		return $upload;
	}

	// Get the watermark text from settings.
	$watermark_text = get_option( 'rtt_watermark_text', 'Your Watermark' );
	if ( empty( $watermark_text ) ) {
		return $upload;
	}

	// Define paths.
	$uploaded_file_path = $upload['file'];
	$output_file_path   = str_replace( '.', '-watermarked.', $uploaded_file_path );

	// Define the FFmpeg command.
	$ffmpeg_path     = '/opt/homebrew/bin/ffmpeg'; // TODO: Update the path to FFmpeg.
	$drawtext_filter = "drawtext=text='" . addslashes( $watermark_text ) .
		"':fontcolor=white:fontsize=32:x=w-text_w-20:y=h-text_h-20";

	if ( $is_video ) {
		$ffmpeg_command = $ffmpeg_path . ' -i ' . escapeshellarg( $uploaded_file_path ) .
		' -vf "' . $drawtext_filter . '" -codec:a copy -codec:v libx264 -crf 18 -preset veryfast ' .
		escapeshellarg( $output_file_path );
	} else {
		$ffmpeg_command = $ffmpeg_path . ' -i ' . escapeshellarg( $uploaded_file_path ) .
			' -vf "' . $drawtext_filter . '" ' .
			escapeshellarg( $output_file_path );
	}

	// Execute the FFmpeg command.
	exec( $ffmpeg_command, $output, $return_var );

	if ( 0 === $return_var && file_exists( $output_file_path ) ) {
		unlink( $uploaded_file_path );
		rename( $output_file_path, $uploaded_file_path );
	}

	return $upload;
}

add_filter( 'wp_handle_upload', 'add_text_watermark_to_media' );
