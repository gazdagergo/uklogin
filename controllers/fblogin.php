<?php
/**
* facebook login
* szükséges config:FB_CLIENT_ID, FB_CLIENT_SECRET
* sikeres login után sessionban lévő redirect_uri  cimre ugrik
*
* a https:developers.facebook.com oldalon kell a klienset regisztrálni
* a "products" részhez adjuk hozzá a "Facebbok login"-t, engedélyezzük,
* ennek beállításait is adjuk meg, "Force Web OAuth Reauthentication" = No legyen
* az fb -n megadandó redirekt uri: MYDOMAIN/opt/fblogin/code/  (fontos a végén a "/")
*
*/

/** fbLogin kontroller osztály */
class FbloginController extends Controller {

    /** konstruktor */
    function __construct() {
        if (!defined('FB_CLIENT_ID')) {
            define('FB_CLIENT_ID','00000000');
            define('FB_CLIENT_SECRET','00000000');
        }
    }

    /**
     * távoli URL hívás
     * @param string $url
     * @param array $post ["név" => "érték", ...]
     * @param array $headers
     * @return string
     */
    protected function callCurl(string $url, array $post=array(), array $headers=array()):string {
        $return = '';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if(count($post)>0) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $return = curl_exec($ch);
        return $return;
    }

    /**
	 * távol API hívás
	 * @param string $url
	 * @param array $post
	 * @param array $headers
	 * @return mixed
	 */
	protected function apiRequest(string $url, array $post=array(), array $headers=array()) {
	        $headers[] = 'Accept: application/json';
	        if (isset($post['access_token'])) {
	            $headers[] = 'Authorization: Bearer ' . $post['access_token'];
	        }
	        $response = $this->callCurl($url, $post, $headers);
	        return JSON_decode($response);
	}

	/**
	 * eljárás miután facebookal sikeresen bejelentkezett
	 * ha a user nem létezik létrehozza, ha már létezik akkor beolvassa
	 * @param string $fbId
	 * @param string $fbName
	 * @param string $fbPicture
	 * @return UserRecord
	 */
	protected function readOrCreateUser(string $fbId, string $fbName, string $fbPicture): UserRecord {
	    $this->getModel('openid'); // user rekord definició
		$user = new UserRecord();
		$w = explode(' ',$fbName);
		$table = new table('oi_users');
		$table->where(['sub','=','fb'.$fbId]);
		$rec = $table->first();
		if ($rec) {
			// megvan a user rekord
			foreach ($rec as $fn => $fv) {
				$user->$fn = $fv;
			}
		} else {
			// még nincs ilyen user rekord, most létrehozzuk
    		$user = new UserRecord();
			$user->sub = 'fb'.$fbId;
			$user->picture = $fbPicture;
			$user->family_name = $fbName;
			$user->pswhash = time();
			$user->code = $user->sub;
			if (count($w) >= 3) {
				$user->family_name = $w[0];
				$user->middle_name = $w[1];
				$user->given_name = $w[2];
			}
			if (count($w) == 2) {
			    $user->family_name = $w[0];
			    $user->middle_name = '';
			    $user->given_name = $w[1];
			}
			$user->nickname = $fbName;
			if (config('OPENID') != 2) {
			    $user->family_name = '';
			    $user->middle_name = '';
			    $user->given_name = '';
			    $user->picture = '';
			}
			$table->insert($user);
			$user->id = $table->getInsertedId();
			$user->nickname = $user->given_name.'-'.$user->id;
			$table->where(['id','=',$user->id]);
			$table->update($user);
		}
		return $user;
	}

	/**
	 * Átirányit a facebook loginra  state -ban a session_id() -t küldi
	 * @param Request $request
	 */
	public function authorize(Request &$request) {
		?>
		<html>
		<body>
		wait please ...
		<div style="display:none">
		<form action="https://www.facebook.com/dialog/oauth" method="get" name="form1" target="_self">
		<input type="text" name="client_id" value="<?php echo FB_CLIENT_ID; ?>" />
		<input type="text" name="state" value="<?php echo session_id(); ?>" />
		<input type="text" name="redirect_uri" value="<?php echo config('MYDOMAIN')?>/opt/fblogin/code/" />
		<!-- ezek a facebooknál nem kellenek más oauth vagy openid szervernél kellhetnek. 
		<input type="text" name="scope" value="id name picture" />
		<input type="text" name="policy_uri" value="<?php echo $request->sessionGet('policy_uri'); ?>" />
		<input type="text" name="response_type" value="code" />
		-->
		<button type="submit">OK</button>
		</form>
		</div>
		<script type="text/javascript">
			document.forms.form1.submit();
		</script>
		</body>
		</html>
		<?php
	}

	/**
	 * facebook oauth hivta vissza
	 * 1. lekérdezi a facebookból az acces_token-t
	 * 2. lekérdezi a facebook-ból a userinfokat
	 * 3. szükség esetén létrehozza a user rekordot
	 * 4. uklogin bejelentkezést csinál
	 * 5. ezután a sessionban lévő redirect_uri -ra ugrik, ez pedig a scope elfogadtató
	 * képernyőt jeleníti majd meg.
	 * sessionban érkezik: client_id, policy_url, scope, redirect_uri, state, nonce
	 * @param Request $request - code, state state-ban a session_id érkezik
	 */
	public function code(Request &$request) {
	    $code = $request->input('code');
	    $state = $request->input('state');
		$this->sessionChange($state, $request);
   	    $token = $this->apiRequest(
   	      'https://graph.facebook.com/oauth/access_token',
   		   ['client_id' => FB_CLIENT_ID,
             'client_secret' => FB_CLIENT_SECRET,
             'redirect_uri' => config('MYDOMAIN').'/opt/fblogin/code/',
             'state' => $state,
             'code' => $code
   		   ]
	    );
		if (isset($token->access_token)) {
            $url="https://graph.facebook.com/v2.3/me?fields=id,name,picture";
            $request->sessionSet('access_token', $token->access_token);
   			$fbuser = $this->apiRequest(
   				$url,
				['access_token' => $token->access_token]
			);
	    	if (!isset($fbuser->error)) {
					// $fbuser alapján bejelentkezik (ha még nincs user rekord létrehozza)
					// $fbuser->name, ->id ->picture->data->url
	    	        $user = $this->readOrCreateUser($fbuser->id, $fbuser->name, $fbuser->picture->data->url);
	    	        $request->sessionSet('loggedUser',$user);
	    	        $url = $request->sessionGet('redirect_uri');
	    	        if (strpos(' '.$url,'?') > 0) {
	    	            $url .= '&code='.$user->code;
	    	        } else {
	    	            $url .= '?code='.$user->code;
	    	        }
	    	        if ($user->audited == 0) {
	    	            ?>
	    	            <html>
	    	            <body>
	    	            <script type="text/javascript">
						alert('Ez egy "nem hitelesített" felhasználói fiók.'+
								'Egyes alkalmazások korlátozhatják az ilyen fiók használatát.'+
								'A profil oldalon a "Hitelesítés" gombnál olvashatsz további információkat.');
						document.location="<?php echo $url; ?>";
	    	            </script>
	    	            </body>
	    	            </html>
	    	            <?php 
	    	        } else {
	    	            redirectTo($url);
	    	        }
			} else {
					echo 'Fatal error in facebook login. wrong user data '.json_encode($fbuser); return;
			}
		 } else {
			echo 'Fatal error in facebook login. access_token not found '.
			'code = '.json_encode($code).
			' response= '.json_encode($token);
			return;
		 }
	}
}
?>
