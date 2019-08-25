<?php
include 'vendor/autoload.php';


class LoginController extends Controller {
    /**
     * szolgáltatás hívása ; login form kirajzolása iframe -be
     * @param object $request 
     * @return void
     */
    public function form($request) {
        if ($request->sessionGet('adminNick') == '') {
            $view = $this->getView('login');
            $p = new stdClass();
            $p->client_id = 12;
            $p->state = $request->input('state', MYDOMAIN.'/opt/appregist/adminform');
            $p->adminNick = $request->sessionget('adminNick','');
            $view->loginForm($p);
        } else {
            echo '<html>
                <body>
                <script type="text/javascript">
                    document.location="'.MYDOMAIN.'/opt/appregist/adminform";
                </script>
                </body>
                </html>
                ';
        }
    }
    
    /**
     adat lekérés távoli url -ről curl POST -al
     * @param string $url
     * @param array $fields
     * @return string
     */
    protected function getFromUrl(string $url, array $fields = []): string {
        $fields_string = '';
        $ch = curl_init();
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string = rtrim($fields_string, '&');
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        return curl_exec($ch);
    }
    
    /**
     * uklogin szolgáltatás callback function
     * sikeres login után: sessionba teszi a nicknevet, redirect adminform
     * @param object $request - code
     * @retrun void
     */
    public function code($request) {
        $code = $request->input('code');
        $state = urldecode($request->input('state',MYDOMAIN));

        // access_token kérése client_id, client_secret, code alapján
        $url = MYDOMAIN.'/oauth2/access_token/';
        if (MYDOMAIN != '') {
            $fields = ["client_id" => "12", "client_secret" => "13", "code" => $code];
            $result = JSON_decode($this->getFromUrl($url,$fields));
        } else {
            $result = new stdClass();
            $result->access_token = '0';
        }
        if ((!isset($result->error)) && (isset($result->access_token))) {
            
            // access_token sikeresen lekérve. Userinfo kérése access_token alapján
            $access_token = $result->access_token;
            $url = MYDOMAIN.'/oauth2/userinfo/';
            $fields = ["access_token" => $access_token];
            if (MYDOMAIN != '') {
                $result = JSON_decode($this->getFromUrl($url,$fields));
            } else {
                $result = new stdClass();
                $result->nick = 'unittest';
            }

            if ($result->nick != 'error') {
                // sikeres login
                $request->sessionSet('adminNick',$result->nick);
                echo '<html>
                <body>
                <script type="text/javascript">
                    parent.document.location="'.$state.'";
                </script>
                </body>
                </html>
                ';
            }
        } else {
            echo 'fatal error in login';
        }
    }
    
    public function logout($request) {
        $request->sessionSet('adminNick','');
        $request->sessionSet('csrToken','');
        if (!headers_sent()) {
            header('Location: '.MYDOMAIN);
        }
    }
}
?>