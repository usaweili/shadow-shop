<?php

if ( isset($_REQUEST['action']) && 'addaccount' == $_REQUEST['action'] ) {
	check_admin_referer( 'add-account', '_wpnonce_add-account' );

	$data_array = array (
		"type" => "SsAccount",
		"uiType" => "wordpress",
		"nodeId" => $_REQUEST['nodeId'],
		"userId" => $_REQUEST['userId'],
		"orderId" => "adminApproval",
		"lifeSpan" => $_REQUEST['lifeSpan'],
		"method" => $_REQUEST['method'],
		"traffic" => (int) $_REQUEST['traffic'],
	);

	$return = Shadowsocks_Hub_Helper::call_api("POST", "http://sshub/api/account", json_encode($data_array));

	$error = $return['error'];
	$http_code = $return['http_code'];
	$response = $return['body'];

	if ($http_code === 201) {
		$redirect = add_query_arg( array(
			'update' => 'add',
		), admin_url('admin.php?page=shadowsocks_hub_accounts') );

		wp_redirect( $redirect );
		die();

	} elseif ($http_code === 400) {
		$error_msg = "Invalid input";
	} elseif ($http_code === 404) {
		$error_msg = "Node does not exist";
	} elseif ($http_code === 500) {
		$error_msg = "Backend system error (addAccountByAdmin)";
	} elseif ($error) {
		$error_msg = "Backend system error: ".$error;
	} else {
		$error_message = "Backend system error undetected error.";
	}
	
	$redirect = add_query_arg( array(
		'error' => urlencode($error_msg),
	), admin_url('admin.php?page=shadowsocks_hub_add_account') );

	wp_redirect( $redirect );
	die();
}
?>
<div class="wrap">
<h1 id="add-new-account"><?php _e( 'Add New Account', 'shadowsocks-hub' ); ?>
</h1>

<?php if ( isset($_REQUEST['error']) ) : ?>
	<div class="error">
		<ul>
		<?php
			$err = urldecode($_REQUEST['error']);
			echo "<li>$err</li>\n";
		?>
		</ul>
	</div>
<?php endif;

if ( ! empty( $messages ) ) {
	foreach ( $messages as $msg )
		echo '<div id="message" class="updated notice is-dismissible"><p>' . $msg . '</p></div>';
} ?>
<?php
	$args = array(
		'fields' => array('ID', 'user_email'),
	);
	$allUsers = get_users($args);

	$return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/node/all", false);

    $error = $return['error'];
    $http_code = $return['http_code'];
	$response = $return['body'];

	$data = array();
	if ($http_code === 200) {
		$allNodes = $response;
	} elseif ($http_code === 500) {
		$error_message = "Backend system error (getAllNodes)";
	} elseif ($error) {
		$error_message = "Backend system error: ".$error;
	} else {
		$error_message = "Backend system error undetected error.";
	}; 

	if ($http_code !== 200) { ?>
		<div class="error">
		<ul>
		<?php
			echo "<li>$error_message</li>\n";
		?>
		</ul>
	</div>
	<?php
	}
	?>


<form method="post" name="addaccount" id="addaccount" class="validate" novalidate="novalidate">
<input name="action" type="hidden" value="addaccount" />
<?php wp_nonce_field( 'add-account', '_wpnonce_add-account' ) ?>

<table class="form-table">
	<tr class="account-userId-wrap">
		<th scope="row"><label for="addaccount-userId"><?php _e('User', 'shadowsocks-hub'); ?></label></th>
		<td><select name="userId" id="user">
			<?php
			foreach($allUsers as $user) {
				echo '<option value ="'.$user->ID.'">'.$user->user_email.'</option>';
			}
			?>
			</select>
		</td>
	</tr>
	<tr class="account-nodeId-wrap">
		<th scope="row"><label for="addaccount-nodeId"><?php _e('Node', 'shadowsocks-hub'); ?></label></th>
		<td><select name="nodeId" id="node">
			<?php
			foreach($allNodes as $node) {
				echo '<option value ="'.$node["id"].'">'.$node["name"].'</option>';
			}
			?>
			</select>
		</td>
	</tr>
	<tr class="account-lifeSpan-wrap">
		<th scope="row"><label for="addaccount-lifeSpan"><?php _e('Life Span', 'shadowsocks-hub'); ?></label></th>
		<td>
			<select name="lifeSpan" id="lifeSpan">
				<option value ="month" selected="selected">one month</option>
				<option value ="bimonth">two months</option>
				<option value ="quarter">three months</option>
				<option value ="semiannual">six months</option>
				<option value ="annual">one year</option>
			</select>
		</td>
	</tr>
	<tr class="account-method-wrap">
		<th scope="row"><label for="addaccount-method"><?php _e('Encryption', 'shadowsocks-hub'); ?></label></th>
		<td>
			<select name="method" id="method">
				<option value ="aes-128-gcm">aes-128-gcm</option>
				<option value ="aes-192-gcm">aes-192-gcm</option>
				<option value ="aes-256-gcm">aes-256-gcm</option>
				<option value ="aes-128-cfb">aes-128-cfb</option>
				<option value ="aes-192-cfb">aes-192-cfb</option>
				<option value ="aes-256-cfb" selected="selected">aes-256-cfb</option>
				<option value ="aes-128-ctr">aes-128-ctr</option>
				<option value ="aes-192-ctr">aes-192-ctr</option>
				<option value ="aes-256-ctr">aes-256-ctr</option>
				<option value ="camellia-128-cfb">camellia-128-cfb</option>
				<option value ="camellia-192-cfb">camellia-192-cfb</option>
				<option value ="camellia-256-cfb">camellia-256-cfb</option>
				<option value ="bf-cfb">bf-cfb</option>
				<option value ="chacha20-ietf-poly1305">chacha20-ietf-poly1305</option>
				<option value ="xchacha20-ietf-poly1305">xchacha20-ietf-poly1305</option>
				<option value ="salsa20">salsa20</option>
				<option value ="chacha20">chacha20</option>
				<option value ="chacha20-ietf">chacha20-ietf</option>
			</select>
		</td>
	</tr>
	<tr class="account-traffic-wrap">
		<th scope="row"><label for="addaccount-traffic"><?php echo __('Traffic', 'shadowsocks-hub'); ?></label></th>
		<td><input type="number" name="traffic" id="traffic" value="" class="regular-text"/></td>
	</tr>
</table>

<?php submit_button( __( 'Add Account', 'shadowsocks-hub' )); ?>
</form>