<?php

namespace XWP\SafeInputDemo;

use XWP\SafeInput\PostMeta;
use XWP\SafeInput\Request;

/**
 * Our custom meta box.
 */
class AwesomeMetaBox {
	const METABOX_ID = 'awesome-post-meta-box';
	const META_KEY = 'awesome-meta-key';
	const INPUT_NAME = 'awesome-input-field-name';
	const NONCE_ACTION = 'awesome-nonce-action';
	const NONCE_INPUT_NAME = 'awesome-nonce-input';

	public function init_hooks() {
		add_action( 'add_meta_boxes', [ $this, 'register' ] );
		add_action( 'save_post', [ $this, 'save' ] );
	}

	public function register() {
		add_meta_box(
			self::METABOX_ID,
			__( 'Awesome Post Meta', 'awesome-post-meta' ),
			[ $this, 'render' ],
			'post',
			'side'
		);
	}

	public function is_selected( $value ) {
		return ( 'yes' === $value );
	}

	public function render( $post ) {
		$meta = new PostMeta( $post->ID );
		$setting = new AwesomeSetting( $post->ID, self::META_KEY );

		if ( ! $meta->can_save() ) {
			return;
		}

		?>
		<label>
			<input
				type="checkbox"
				value="yes"
				name="<?php echo esc_attr( self::INPUT_NAME ); ?>"
				<?php checked( $setting->get() ); ?>
				/>
			<?php esc_html_e( 'Mark this post as awesome', 'awesome-post-meta' ); ?>
		</label>
		<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_INPUT_NAME ); ?>
		<?php
	}

	public function save( $post_id ) {
		$request = new Request( INPUT_POST );
		$meta = new PostMeta( $post_id );

		if ( $request->verify_nonce( self::NONCE_ACTION, self::NONCE_INPUT_NAME ) && $meta->can_save() ) {
			$setting = new AwesomeSetting( $post_id, self::META_KEY );

			if ( $this->is_selected( $request->param( self::INPUT_NAME ) ) ) {
				$setting->set();
			} else {
				$setting->delete();
			}
		}
	}

}
