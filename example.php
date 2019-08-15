<?php
/*
 * Példa program az uklogin használatára
 * URL elérése: <MYDOMAIN>/example.php
 * Hivásai:
 * 1. paraméter nélkül: képernyő kirajzolás
 * 2. task=code&code=xxxxxx  az uklogin hivta vissza, ilyenkor iframe -ben fut
 * (az app regisztrálásakor megadott callback URL: <MYDOMAIN>/example.php?task=code
 */
session_start();

// lokális teszt vagy éles szerver?.
if ($_SERVER['SERVER_NAME'] == 'robitc') {
	// lokális teszt
	define('MYDOMAIN','http://robitc/uklogin');
	define('UKLOGINDOMAIN','http://robitc/uklogin/oauth2');
} else {
	// éles szerver
	define('MYDOMAIN','https://uklogin.tk');
	define('UKLOGINDOMAIN','https://uklogin.tk/oauth2');
}
define('CLIENTID','0000000000');
define('CLIENTSECRET','0000000000');


if (isset($_GET['task'])) {
    $task = $_GET['task'];
} else {
    $task = 'home';
}

if ($task == 'code') {
    // oauth2 hivta vissza, ilyenkor az iframe -ben fut
    echo '<!doctype html>
        <html>
        <head>
        <title>uklogin example</title>
        </head>
        <body>
        ';
    // access_token kérése
    $code = $_GET['code'];
    $url = UKLOGINDOMAIN.'/access_token/client_id/'.CLIENTID.'/client_secret/'.CLIENTSECRET.'/code/'.$code;
    $result = JSON_decode(implode("\n", file($url)));
    if ((!isset($result->error)) && (isset($result->access_token))) {

        // access_token sikeresen lekérve. Userinfo kérése
        $access_token = $result->access_token;
        $url = UKLOGINDOMAIN.'/userinfo/access_token/'.$access_token;
        $result = JSON_decode(implode("\n", file($url)));
        if ($result->nick != 'error') {
            // Userinfo sikeresen lekérve. A user sikeresen bejelentkezett.
            // Ha másik oldalt kell behivni akkor vegyük figyelembe, hogy most az
            // iframe -ben fut a program! JS: parent.document.location="xxxx" használható.
            $session['userNick'] = $result->nick;
            ?>
            <h2>"<?php echo $result->nick?>"&nbsp;sikeresen bejelentkezett.</h2>
            <h1 style="text-align:center">:)</h1>
            </body>
    		</html>
    		<?php
        } else {
            echo '<p class="alert alert-danger">Error in get userinfo</p>
            </body>
            </html>';
            exit();
        }
    } else {
            echo '<p class="alert alert-danger">Error in get access_token</p>
            </body>
            </html>';
            exit();
    }
} // task=code

if ($task == 'home') {
?>
<!doctype html>
<html lang="hu">
  <head>
    <base href="<?php echo MYDOMAIN; ?>" target="_blank">
    <meta charset="utf-8">
    <meta name="title" content="Example Ügyfélkapus login rendszer">
    <meta name="description" content="Example web szolgáltatás e-demokrácia programok számára. Regisztráció ügyfélkapus aláírás segitségével.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>example uklogin</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

	 <!-- bootstrap -->
	 <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
	 <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	 <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
	 <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>

	 <!-- awesome font -->
	 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	 <!-- font -->
	 <link href='https://fonts.googleapis.com/css?family=Grand+Hotel' rel='stylesheet' type='text/css'>

	 <style type="text/css">
        /* desctop */
       @media (min-width: 577px) {
           #scope {padding:5px}
    	   .main {padding:20px;}
    	   #popup {position:absolute; z-index:99; display:none;
    	     top:130px; left:15px; width:510px; max-width:510px; height:800px;
    	     background-color:white; margin:10px; border-style:solid; border-width:2px; border-color:black;}
    	   #popup iframe {border-style:none; width:500px; height:770px}
    	   #popupHeader {text-align:right;}
    	   #events {border-style:none}
    	   #sourceTitle {position:absolute; z-index:98; top:360px; left:100px; color:white;}
    	   #source {position:absolute; z-index:98; top:300px; left:50px; width:80%; background-color:white}
    	   #logo {width:100%}
    	   .demoInfo {position:absolute; z-index:60; top:300px; left:100px; width:600px; height:auto;
    	       background-color:silver; padding:10px;  opacity:0.5; color:black;}
	   }

       /* phone */
       @media (max-width: 576px) {
           #scope {padding:5px}
    	   .main {padding:10px;}
    	   #popup {position:absolute; z-index:99; display:none;
    	     top:15px; left:15px; width:95%; max-width:550px; height:900px;
    	     background-color:white; margin:5px; border-style:solid; border-width:2px; border-color:black;}
    	   #popup iframe {border-style:none;  width:95%; height:850px}
    	   #popupHeader {text-align:right;}
    	   #events {border-style:none}
    	   #sourceTitle {position:absolute; z-index:98; top:360px; left:50px; color:white;}
    	   #source {position:absolute; z-index:98; top:300px; left:50px; width:80%; background-color:white}
    	   #logo {width:100%}
    	   .demoInfo {position:absolute; z-index:60; top:30px; left:30px;
    	       width:80%; height:auto;
    	       background-color:silver; padding:50px;  opacity:0.5; color:black;}
       }


	 </style>

  </head>
  <body>
  	<div class="main">
  		<h1>
  			Ügyfélkapus bejelentkezés példa program
  		</h1>
			<nav class="navbar navbar-expand-lg navbar-light bg-light">
			  <div class="collapse navbar-collapse" id="navbarNav">
			    <ul class="navbar-nav">
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="<?php echo UKLOGINDOMAIN; ?>">
			        	<em class="fa fa-home"></em>&nbsp;Kezdőlap<span class="sr-only">(current)</span></a>
			      </li>
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="" id="linkRegist">
			        	<em class="fa fa-plus"></em>&nbsp;Regisztrálás</a>
			      </li>
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="" id="linkLogin">
			        	<em class="fa fa-key"></em>&nbsp;Bejelentkezés</a>
			      </li>
			    </ul>
			  </div>
			  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			    <span class="navbar-toggler-icon"></span>
			  </button>
			</nav>
  			<div style="text-align:center">
  				<img src="<?php echo MYDOMAIN; ?>/templates/default/logo.jpg" id="logo" />
  			</div>
  			<div id="popup">
  				<div id="popupHeader">
  				 <em class="fa fa-close" style="cursor:pointer" title="close"
  					onclick="$('#popup').toggle();"></em>&nbsp;
  				</div>
  				<iframe id="ifrm1" src=""></iframe>
  			</div>
          	<h4 id="sourceTitle">Forrás program <em class="fa fa-code" style="cursor:pointer"
          		onclick="$('#source').toggle();" title="view"></em>
          	</h4>
          	<div id="source" style="display:none">
  				<div id="popupHeader">
  				 <em class="fa fa-close" style="cursor:pointer" title="close"
  					onclick="$('#source').toggle();"></em>&nbsp;
  				</div>
              	<textarea style="width:100%; height: 550px" readonly="readonly">
              		<?php
              		    echo "\n";
              		    $lines = file('example.php');
              		    foreach ($lines as $line) {
              		        $line = str_replace('<', '&lt;', $line);
              		        $line = str_replace('>', '&gt;', $line);
              		        echo $line;
              		    }
              		?>
              	</textarea>
          	</div>
  	</div>

  	<script type="text/javascript">
		$(function() {
			$('#linkRegist').click(function() {
				$('#ifrm1').attr('src',"<?php echo MYDOMAIN; ?>/oauth2/registform/client_id/<?php echo CLIENTID; ?>");
				$('#popup').show();
				return false;
			});
			$('#linkLogin').click(function() {
				$('#ifrm1').attr('src',"<?php echo MYDOMAIN; ?>/oauth2/loginform/client_id/<?php echo CLIENTID; ?>");
				$('#popup').show();
				return false;
			});
		});
		function logged(nick) {
            // a valós applikációkban ilyenkor gyakran JS koddal behiv egy másik oldalt, itt most csak
            // bezárja a popup -ot, és üzenetet ir ki.
			$('#popup').hide();
			alert(nick+' bejelentkezett.');
		}
  	</script>

  </body>
</html>
<?php
} // task=home
?>
