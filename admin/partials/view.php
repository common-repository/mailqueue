<div class="wrap mailqueue-settings">
	<h1><?php echo $heading ?></h1>
	<div class="tablinks">
		<button class="tablink" data-tab="settings"><?php _e( 'Settings', 'mailqueue' ); ?></button>
		<button class="tablink" data-tab="test-email"><?php _e( 'Test email', 'mailqueue' ); ?></button>
	</div>
	<div id="settings" class="tab">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<form action="options.php" method="post">
					<?php settings_fields( $settings_group ); ?>
					<table class="form-table">
						<?php echo $fields ?>
					</table>
					<div class="submit-wrap">
						<?php submit_button( $submit_text ); ?>
						<div class="spinner"></div>
					</div>
				</form>
			</div>
		</div>
		<br class="clear">
	</div>
	<div id="test-email" class="tab d-none">
		<form class="test-email-data">
			<table class="form-table">
				<tr>
					<th>
						<label for="email"><?php _e( 'Email', 'mailqueue' ); ?></label>
					</th>
					<td>
						<input type="email" id="email" name="email">
					</td>
				<tr>
				<tr>
					<th>
						<label for="subject"><?php _e( 'Subject', 'mailqueue' ); ?></label>
					</th>
					<td>
						<input type="text" id="subject" name="subject">
					</td>
				<tr>
				<tr>
					<th>
						<label for="message"><?php _e( 'Message', 'mailqueue' ); ?></label>
					</th>
					<td>
						<textarea id="message" name="message"></textarea>
					</td>
				<tr>
			</table>
			<div class="response"></div>
			<div class="submit-wrap">
				<button id="send-test-email" class="button button-primary"><?php _e( 'Send test email', 'mailqueue' ); ?></button>
				<div class="spinner"></div>
			</div>
		</form>
	</div>
</div>
