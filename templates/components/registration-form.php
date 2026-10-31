<?php
/**
 * Registration form template.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array<string, mixed> $data {
 *     State-specific view data.
 *
 *     @type string       $state          Form state.
 *     @type string       $core_fields    Fields other plugins added through register_form, scripts removed.
 *     @type string|null  $account_url    Account URL.
 *     @type string|null  $login_url      Sign-in URL.
 *     @type array         $error          Error data.
 *     @type bool          $show_name      Whether to show the name field.
 *     @type string        $nonce_action   Nonce action.
 *     @type string        $nonce_field    Nonce field name.
 *     @type string        $action_field   Hidden action field name.
 *     @type string        $email_field    Email field name.
 *     @type string        $name_field     Name field name.
 *     @type string        $honeypot_field Honeypot field name.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="releasecart-component releasecart-registration-form">
	<?php // Keep the card width independent from the theme content column. ?>
	<div class="releasecart-registration-form__card releasecart-card">
	<?php if ( 'signed_in' === $data['state'] ) : ?>

		<div class="releasecart-registration-form__signed-in">
			<p><?php echo esc_html__( 'You are already signed in.', 'releasecart' ); ?></p>
			<?php if ( null !== $data['account_url'] ) : ?>
				<a class="releasecart-registration-form__account" href="<?php echo esc_url( $data['account_url'] ); ?>">
					<?php echo esc_html__( 'Go to your account', 'releasecart' ); ?>
				</a>
			<?php endif; ?>
		</div>

	<?php elseif ( 'closed' === $data['state'] ) : ?>

		<p class="releasecart-registration-form__closed">
			<?php echo esc_html__( 'Registration is currently closed.', 'releasecart' ); ?>
		</p>

	<?php elseif ( 'success' === $data['state'] ) : ?>

		<div class="releasecart-registration-form__success">
			<p><?php echo esc_html__( 'Thanks for registering. Check your email for a link to set your password.', 'releasecart' ); ?></p>
			<?php if ( null !== $data['login_url'] ) : ?>
				<a href="<?php echo esc_url( $data['login_url'] ); ?>">
					<?php echo esc_html__( 'Go to sign in', 'releasecart' ); ?>
				</a>
			<?php endif; ?>
		</div>

	<?php else : ?>

		<?php if ( $data['error']['show'] ) : ?>
			<p class="releasecart-registration-form__error releasecart-notice releasecart-notice--error" role="alert">
				<?php echo esc_html( $data['error']['text'] ); ?>
			</p>
		<?php endif; ?>

		<form class="releasecart-registration-form__form" method="post">
			<?php wp_nonce_field( $data['nonce_action'], $data['nonce_field'] ); ?>
			<input type="hidden" name="<?php echo esc_attr( $data['action_field'] ); ?>" value="1" />

			<p class="releasecart-registration-form__field releasecart-field">
				<label for="releasecart-register-email"><?php echo esc_html__( 'Email address', 'releasecart' ); ?></label>
				<input type="email" class="releasecart-input" id="releasecart-register-email" name="<?php echo esc_attr( $data['email_field'] ); ?>" required autocomplete="email" />
			</p>

			<?php if ( $data['show_name'] ) : ?>
				<p class="releasecart-registration-form__field releasecart-field">
					<label for="releasecart-register-name"><?php echo esc_html__( 'Name (optional)', 'releasecart' ); ?></label>
					<input type="text" class="releasecart-input" id="releasecart-register-name" name="<?php echo esc_attr( $data['name_field'] ); ?>" autocomplete="name" />
				</p>
			<?php endif; ?>

			<?php echo wp_kses( $data['core_fields'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() ); ?>

			<?php
			// The honeypot: hidden from people, tempting to bots. A real visitor
			// never sees or fills it; a non-empty value on submit is discarded
			// server-side. aria-hidden and tabindex keep it out of the a11y tree
			// and the tab order.
			?>
			<div class="releasecart-registration-form__hp" aria-hidden="true">
				<label for="releasecart-register-website"><?php echo esc_html__( 'Leave this field empty', 'releasecart' ); ?></label>
				<input type="text" id="releasecart-register-website" name="<?php echo esc_attr( $data['honeypot_field'] ); ?>" tabindex="-1" autocomplete="off" />
			</div>

			<p class="releasecart-registration-form__submit">
				<button type="submit" class="releasecart-button releasecart-button--primary"><?php echo esc_html__( 'Create account', 'releasecart' ); ?></button>
			</p>
		</form>

		<?php if ( null !== $data['login_url'] ) : ?>
			<p class="releasecart-registration-form__sign-in">
				<a href="<?php echo esc_url( $data['login_url'] ); ?>">
					<?php echo esc_html__( 'Already have an account? Sign in', 'releasecart' ); ?>
				</a>
			</p>
		<?php endif; ?>

	<?php endif; ?>
	</div>
</div>
