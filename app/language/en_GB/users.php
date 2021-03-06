<?php

return [

	'users' => 'Users',

	'create_user' => 'Create a new user',
	'add_user' => 'Add a new user',
	'editing_user' => 'Editing %s&rsquo;s Profile',
	'remembered' => 'I know my password',
	'forgotten_password' => 'Forgotten your password?',

	// roles
	'administrator' => 'Admin',
	'administrator_explain' => '',

	'editor' => 'Editor',
	'editor_explain' => '',

	'user' => 'User',
	'user_explain' => '',

	// form fields
	'real_name' => 'Real Name',
	'real_name_explain' => '',

	'bio' => 'Biography',
	'bio_explain' => '',

	'status' => 'Status',
	'status_explain' => '',

	'role' => 'Role',
	'role_explain' => '',

	'username' => 'Username',
	'username_explain' => '',
	'username_missing' => 'Please enter a username, must be at least %s characters',

	'password' => 'Password',
	'password_explain' => '',
	'password_too_short' => 'Password must be at least %s characters',

	'new_password' => 'New Password',

	'email' => 'Email',
	'email_explain' => '',
	'email_missing' => 'Please enter a valid email address',
	'email_not_found' => 'Profile not found.',

	// messages
	'updated' => 'User profile updated.',
	'created' => 'User profile created.',
	'deleted' => 'User profile deleted.',
	'delete_error' => 'You cannot delete your own profile',
	'login_error' => 'Username or password is wrong.',
	'logout_notice' => 'You are now logged out.',
	'recovery_sent' => 'We have sent you an email to confirm your password change.',
	'recovery_expired' => 'Password recovery token has expired, please try again.',
	'password_reset' => 'Your new password has been set. Username: <kbd>%s</kbd>',

	// password recovery email
	'msg_not_send' => 'Message could not be sent: <code>%s</code>',
	'recovery_subject' => 'Password Reset',
	'recovery_message' => 'You or somebody have requested to reset your password.' .
		'To continue follow the link below:<br><br>' . PHP_EOL . '%s' . '<br>If you don\'t requested before just ignore this email.',

	// profile
	'profile' => 'Profile',
	'please_login' => 'Please sign in',
	'your_email' => 'Your Email',

	// confirmation
	'confirm' => 'Your information has been validate! Thank you for your cooperation.',
	'confirm_not_found' => 'Sorry, profile not found',
	'already_confirm' => 'You have already made a confirmation before. Please login using your username: <kbd>%s</kbd>',
	'confirm_sent' => 'We have sent you an email to confirm your profile info.',

];
