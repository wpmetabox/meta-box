document.addEventListener( 'DOMContentLoaded', function () {
	const toggleButtons = document.querySelectorAll( '.rwmb-password-toggle' );

	toggleButtons.forEach( button => {
		const eyeIcon = button.querySelector( '.rwmb-eye-icon' );
		const eyeOffIcon = button.querySelector( '.rwmb-eye-off-icon' );
		const input = button.previousElementSibling;

		button.addEventListener( 'click', () => {
			if ( input.type === 'password' ) {
				input.type = 'text';
				eyeIcon.style.display = 'none';
				eyeOffIcon.style.display = 'block';
			} else {
				input.type = 'password';
				eyeIcon.style.display = 'block';
				eyeOffIcon.style.display = 'none';
			}
		} );
	} );
} );