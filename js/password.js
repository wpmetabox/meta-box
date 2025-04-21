document.addEventListener( 'DOMContentLoaded', function () {
	const toggleButtons = document.querySelectorAll( '.rwmb-password-toggle' );

	toggleButtons.forEach( button => {
		button.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			const input = document.getElementById( this.dataset.for );
			const eyeIcon = this.querySelector( '.rwmb-eye-icon' );
			const eyeOffIcon = this.querySelector( '.rwmb-eye-off-icon' );

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