<?php if (!(isset($_GET['static']) && $_GET['static'] == false)) {
	header('Refresh:5; url=/');
	echo '<META http-equiv="refresh" content="5; URL=/">';
} ?>
<div class="main">
	<div class="content">
		<h1 class="notification error">
			<?php
			echo 'Error ' . $_GET['code'] . ' : <br>';
			switch ($_GET['code']) {
				case '400':
					echo 'HTTP analysis failed!';
					break;

				case '401':
					echo 'The username or password is incorrect!';
					break;

				case '402':
					echo 'The client must reformulate their request with the correct payment data!';
					break;

				case '403':
					echo 'Access forbidden!';
					break;

				case '404':
					echo 'The page does not exist or no longer exists!';
					break;

				case '405':
					echo 'Method not allowed!';
					break;

				case '406':
					echo 'The request could not be completed in time!';
					break;

				case '500':
					echo 'Internal server error or server overloaded!';
					break;

				case '501':
					echo 'The server does not support the requested service!';
					break;

				case '502':
					echo 'Bad gateway!';
					break;

				case '503':
					echo 'Service unavailable!';
					break;

				case '504':
					echo 'Response took too long!';
					break;

				case '505':
					echo 'HTTP version not supported!';
					break;

				default:
					echo 'Unknown error!';
					break;
			}
			if (!(isset($_GET['static']) && $_GET['static'] == false)) {
				echo '<br>You will be redirected to the homepage in 5 seconds.';
			}

			?>
		</h1>
	</div>
</div>