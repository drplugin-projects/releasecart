<?php
/**
 * Login form template.
 *
 * Theme override: {theme}/releasecart/components/login-form.php.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array<string, mixed> $data {
 *     @type bool $logged_in Login state.
 *     @type string $display_name Display name.
 *     @type string|null $account_url Account link.
 *     @type string $logout_url Logout link.
 *     @type bool $login_error Show sign-in error.
 *     @type string $login_form Login markup.
 *     @type string $lostpassword_url Password-reset link.
 *     @type string|null $registration_url Registration link.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="releasecart-component releasecart-login-form">
	<?php // Keep the width cap outside the theme content column. ?>
	<div class="releasecart-login-form__card releasecart-card">
	<?php if ( $data['logged_in'] ) : ?>

		<div class="releasecart-login-form--logged-in">
			<p class="releasecart-login-form__greeting">
				<?php
				printf(
					/* translators: %s: the logged-in visitor's display name. */
					esc_html__( 'Logged in as %s.', 'releasecart' ),
					esc_html( $data['display_name'] )
				);
				?>
			</p>
			<?php if ( null !== $data['account_url'] ) : ?>
				<a class="releasecart-login-form__account" href="<?php echo esc_url( $data['account_url'] ); ?>">
					<?php echo esc_html__( 'Your account', 'releasecart' ); ?>
				</a>
			<?php endif; ?>
			<a class="releasecart-login-form__logout" href="<?php echo esc_url( $data['logout_url'] ); ?>">
				<?php echo esc_html__( 'Log out', 'releasecart' ); ?>
			</a>
		</div>

	<?php else : ?>

		<?php if ( $data['login_error'] ) : ?>
			<p class="releasecart-login-form__error releasecart-notice releasecart-notice--error" role="alert">
				<?php echo esc_html__( 'Sign-in failed. Check your username and password and try again.', 'releasecart' ); ?>
			</p>
		<?php endif; ?>

		<?php
		// The form subset keeps the login fields and plain form markup, and drops scripts and handlers.
		echo wp_kses( $data['login_form'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::form() );
		?>

		<p class="releasecart-login-form__lost-password">
			<a href="<?php echo esc_url( $data['lostpassword_url'] ); ?>">
				<?php echo esc_html__( 'Lost your password?', 'releasecart' ); ?>
			</a>
		</p>

		<?php if ( null !== $data['registration_url'] ) : ?>
			<p class="releasecart-login-form__register">
				<a href="<?php echo esc_url( $data['registration_url'] ); ?>">
					<?php echo esc_html__( 'Create an account', 'releasecart' ); ?>
				</a>
			</p>
		<?php endif; ?>

	<?php endif; ?>
	</div>
</div>
