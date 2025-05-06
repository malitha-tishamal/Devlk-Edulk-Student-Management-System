$(document).ready(function(){
	$('#newPassword, #confirmPassword').on('keyup', function(){
		let newPassword = $('#newPassword').val();
		let confirmPassword = $('#confirmPassword').val();
		
		if (((newPassword.trim() !== "") && (confirmPassword.trim() !== "")) && newPassword !== confirmPassword) {
			$('#confirmNewPasswordErrorMessage').html('* Unmatched new password and re-entered new password');
			$('#confirmNewPasswordErrorMessage').addClass('pt-1');
		}else{
			$('#confirmNewPasswordErrorMessage').html('');
		}
	});
});

function togglePasswordVisibility(inputId, iconClass) {
	var passwordInput = document.getElementById(inputId);
	var icon = document.querySelector('.' + iconClass);
	
	passwordInput.type = (passwordInput.type === 'password') ? 'text' : 'password';
	icon.classList.toggle('bxs-hide');
	icon.classList.toggle('bxs-show');
}

