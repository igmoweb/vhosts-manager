<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

global $filename, $filename_copy, $filename_apache;
$filename = 'C:\Windows\System32\drivers\etc/hosts';
$filename_copy = 'C:\Windows\System32\drivers\etc/hosts-copy';
$filename_apache = 'C:\wamp\bin\apache\apache2.2.22\conf\extra/httpd-vhosts.conf';

$lines = file($filename_apache);
$config = array();


function manager_redirect() {
	$uri = $_SERVER['REQUEST_URI'];
	header("Location: $uri");
	die();
}

function manager_backup() {
	global $filename, $filename_copy;
	copy( $filename, $filename_copy );
}

function manager_put_contents( Array $new_content ) {
	global $filename;
	file_put_contents( $filename, implode( $new_content ) );
}

function manager_put_apache_contents( $new_content ) {
	global $filename_apache;
	file_put_contents( $filename_apache, $new_content );
}



$file = file( $filename );
$apache_file = file( $filename_apache );

if ( ! empty( $_POST['save-apache'] ) ) {
	manager_put_apache_contents( $_POST['apache-file'] );
	$apache_file = file( $filename_apache );
	manager_redirect();
}

if ( ! empty( $_POST['restore'] ) ) {
	$file = file( $filename_copy );
	manager_put_contents( $file );

	manager_redirect();
}

if ( ! empty( $_POST['add'] ) ) {
	if ( ! empty( $_POST['host'] ) && ! empty( $_POST['host-location']) ) {
		manager_backup();
		$host = stripslashes( $_POST['host'] );
		$host_location = stripslashes( $_POST['host-location'] );
		$file[] = "127.0.0.1\t" . $host . PHP_EOL;

		$apache_file[]  = '<VirtualHost *:80>' . PHP_EOL;
		$apache_file[]  = '    DocumentRoot "' . $host_location . '"' . PHP_EOL;
		$apache_file[]  = '    ServerName ' . $host . PHP_EOL;
		$apache_file[]  = '</VirtualHost>' . PHP_EOL . PHP_EOL;

		manager_put_contents( $file );
		manager_put_apache_contents( implode( $apache_file ) );
	}
	
}

if ( ! empty( $_POST['delete'] ) && ! empty( $_POST['line'] ) ) {
	$_lines = $_POST['line'];
	$lines = array_map( 'intval', $_lines );

	foreach ( $lines as $line ) {
		unset( $file[ $line - 1 ] );
	}

	manager_backup();

	manager_put_contents( $file );
	
	manager_redirect();
}


$vhosts = array();



$i = 1;
foreach ( $file as $line ) {
	preg_match_all( '/^127.0.0.1(.*)/', $line, $matches );
	if ( ! empty( $matches[1] ) ) {
		$vhosts[ $i ] = trim( $matches[1][0] );
	}
	$i++;
}




?>
	<form action="" method="post" id="hosts-form">
		<h2>Hosts</h2>
		<div class="form-controls-wrap">
			<div class="controls">
				<input type="text" name="host" placeholder="Host"><br/> 
				<input type="text" name="host-location" placeholder="Host location"><br/>
				<input class="button" type="submit" name="add" value="Add" />
			</div>
			<div class="controls">
				<p>Delete selected hosts (you'll need to delete manually the virtua Host from Apache conf)</p>
				<input class="button" type="submit" name="delete" value="Delete" onclick="confirm('Are you sure?');" />
			</div>
			<div class="controls">
				<p>Restore hosts file to previous state</p>
				<input class="button" type="submit" name="restore" value="Restore" />
			</div>
			<div class="clear"></div>
		</div>
		
		<table>
			<?php foreach ($vhosts as $line => $host): ?>
				<tr>
					
					<td><input type="checkbox" id="line-<?php echo $line; ?>" name="line[<?php echo $line; ?>]" value="<?php echo $line; ?>"></td>
					<td><label for="line-<?php echo $line; ?>"><?php echo $host; ?></label></td>
					
				</tr>
				
			<?php endforeach; ?>
		</table>
		
	</form>

	<form action="" method="post" id="apache-form">
		<h2>Apache</h2>
		<div class="form-controls-wrap">
			<div class="controls">
				<p>Save conf file</p>
				<input class="button" type="submit" name="save-apache" value="Save" />
			</div>
			<div class="clear"></div>
		</div>
		<textarea name="apache-file" id="apache-file" ><?php echo implode($apache_file); ?> </textarea>
	</form>
	<style>
		p {
			font-size:12px;
		}
		.clear {
			clear:both;
		}
		.form-controls-wrap {
			border:1px solid #EDEDED;
			padding:10px;
			margin-bottom:1em;
		}
		.form-controls-wrap input {
			margin-bottom:5px;
		}

		.form-controls-wrap .controls {
			float:left;
			border-right:1px solid #DEDEDE;
			padding-right:10px;
			padding-left:10px;
			height:13%;
			width:30%;
			vertical-align: middle;
			text-align: center;
		}

		#hosts-form {
			float:left;width:45%
		}
		#apache-form {
			float:right;width:45%	
		}
		table {
			border-collapse: collapse;
			border: 1px solid #333;
		}
		#hosts-form td {
			padding:3px;
			border-bottom:1px solid #333;
		}
		#apache-file {
			width:100%;
			height:100%;
		}
		.button {
			-moz-box-shadow:inset 0px 1px 0px 0px #fed897;
			-webkit-box-shadow:inset 0px 1px 0px 0px #fed897;
			box-shadow:inset 0px 1px 0px 0px #fed897;
			background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #f6b33d), color-stop(1, #d29105) );
			background:-moz-linear-gradient( center top, #f6b33d 5%, #d29105 100% );
			filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#f6b33d', endColorstr='#d29105');
			background-color:#f6b33d;
			-webkit-border-top-left-radius:0px;
			-moz-border-radius-topleft:0px;
			border-top-left-radius:0px;
			-webkit-border-top-right-radius:0px;
			-moz-border-radius-topright:0px;
			border-top-right-radius:0px;
			-webkit-border-bottom-right-radius:0px;
			-moz-border-radius-bottomright:0px;
			border-bottom-right-radius:0px;
			-webkit-border-bottom-left-radius:0px;
			-moz-border-radius-bottomleft:0px;
			border-bottom-left-radius:0px;
			text-indent:0;
			border:1px solid #eda933;
			display:inline-block;
			color:#ffffff;
			font-size:15px;
			font-weight:bold;
			font-style:normal;
			height:40px;
			line-height:40px;
			width:100px;
			text-decoration:none;
			text-align:center;
			text-shadow:1px 1px 0px #cd8a15;
			font-family: Helvetica, Arial;
		}
		.button:hover {
			background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #d29105), color-stop(1, #f6b33d) );
			background:-moz-linear-gradient( center top, #d29105 5%, #f6b33d 100% );
			filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#d29105', endColorstr='#f6b33d');
			background-color:#d29105;
			cursor:pointer;
		}.button:active {
			position:relative;
			top:1px;
		}
	</style>
<?php

