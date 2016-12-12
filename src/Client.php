<?php
class Client
{
	protected $_config;

	protected $_callback;

	protected $_reCheckTime;
	/*
	 * Constructer
	 * @return none
	 */
	public function __construct($config)
	{
		if($this->checkConfig($config))
			$this->_config = $config;
		$this->_reCheckTime = $this->_config['reCheckTime'];
		$this->_callback = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
	}

	/*
	 * check ticket in session / cookie
	 * @return redirect or bool
	 */
	public function isLogin()
	{
		$ticket = $this->getTicket();
		logger('用户凭证：'.var_export($ticket,true)); // debug
		if(isset($ticket['id'])){
			logger('存在用户凭证....');
			if(($ticket['expire_time']+$ticket['create_at']) > time()){
				logger('用户凭证在有效期内....');
				$leftTimes = (time()-$ticket['check_at'])/$this->_reCheckTime;
				if($leftTimes < 1){
					logger('检查时间间隔：'.$this->_reCheckTime);
					logger('不需要向服务器核验用户凭证...'."\n");
					return true;
				}else{
					logger('需要向服务器核验用户凭证...'."\n");
					$this->toServerIsLogin();
					return;
				}
			}
			logger('用户凭证bu在有效期内....'."\n");
			$this->toServerIsLogin();
			return;
		}
		logger('bu存在用户凭证....'."\n");
		$this->toServerIsLogin();
		return;
	}

	/*
	 * get user login info, set cookie and session
	 * @return bool
	 */
	public function login($request)
	{
		// check Server Signature
		if(!$this->checkSignature($request))
			return false;
		logger('获取用户信息...');
		$this->setTicket($request);
		logger('设置用户登录信息完成...'."\n");
		return true;
	}

	/*
	 * get user logout info, delete cookie and session
	 * @return bool
	 */
	public function toLogout($request)
	{
		// check Server Signature
		if(!$this->checkSignature($request))
			return false;
		$this->deleteTicket();
	}

	/*
	 * delete ticket
	 * @return none
	 */
	public function logout()
	{
		$this->deleteTicket();
		$this->toServerLogout();
	}

	/*
	 * get SESSION['ticket'] or COOKIE['ticket']
	 * @return array or NULL
	 */
	private function getTicket()
	{
		$name = $this->_config['ticketName'];
		$checks = $this->_config['ticketChecks'];

		switch($checks){
			case 'all':
				$ticket = $_SESSION[$name];
				if(!isset($ticket))
					$ticket = $_COOKIE[$name];
				break;
			case 'cookie':
				$ticket = $_COOKIE[$name];
				break;
			case 'session':
				$ticket = $_SESSION[$name];
				break;
		}

		return $ticket;
	}

	/*
	 * check config
	 * @return bool
	 */
	private function checkConfig($config)
	{
		return true;
	}

	/*
	 * check client signature
	 * @return bool
	 */
	private function checkSignature($request)
	{
		$serverToken = $this->_config['serverToken'];
		$sigature = $this->getSign($serverToken,$request['nonce'],$request['timestamp']);
		if($sigature == $request['signature'])
			return true;
		return false;
	}

	/*
	 * get encrypt signature
	 * @return string
	 */
	private function getSign($nonce,$timestamp,$token)
	{ 
		$array = array($nonce,$timestamp,$token);
		sort($array);
		$sign = sha1(implode($array));
		return $sign;
	}

	/*
	 * Rediect to Server Login Page
	 * @return redirect
	 */
	private function toServerLogin()
	{
		$loginUrl = $this->_config['loginView'].$this->signature().'&appId='.$this->_config['appId'].'&callback='.$this->_callback;
		header('Location:'.$loginUrl);
	}

	/*
	 * Rediect to Server isLogin Controller
	 * @return redirect
	 */
	private function toServerIsLogin()
	{
		$isLoginUrl = $this->_config['isLogin'].$this->signature().'&appId='.$this->_config['appId'].'&callback='.$this->_callback;
		header('Location:'.$isLoginUrl);
	}

	/*
	 * Rediect to Server Logout Controller
	 * @return redirect
	 */
	private function toServerLogout()
	{
		$logoutUrl = $this->_config['logout'].$this->signature().'&appId='.$this->_config['appId'];
		header('Location:'.$logoutUrl);
	}

	/*
	 * make Client signature
	 * @return string
	 */
	private function signature()
	{
		$nonce = $this->getRand();
		$timestamp = time();
		$token = $this->_config['token'];

		$sigature = $this->getSign($nonce,$timestamp,$token);

		return '?sigature='.$sigature.'&nonce='.$nonce.'&timestamp='.$timestamp;
	}

	/*
	 * get random string
	 * @return string
	 */
	private function getRand($num = 6){
		$string = '';
		$source = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_';
		$length = strlen($source);
		while(strlen($string) < $num){
			$string .= substr($source,rand(0,1000)%$length,1);
		}
		return $string;
	}

	/*
	 * save ticket in session and cookie
	 * decrypt user info
	 * @return none
	 */
	private function setTicket($request)
	{
		$ticket = array(
				'id' => $request['ticket'],
				'create_at' => $request['create_at'],
				'expire_time' => $request['expire_time'],
				'check_at' => $request['check_at']
			);
		$this->setCookie($ticket);
		$userInfo = $this->chanslate_add_to_empty($request['user']);
		$userInfo = $this->decrypt($userInfo);
		$userInfo = $this->chanslate_json_to_array($userInfo);
		$this->setSession($ticket,$userInfo);
	}

	/*
	 * save ticket in session
	 * @return none
	 */
	private function setCookie($ticket)
	{
		$name = $this->_config['ticketName'];
		$expire_time = (int)$ticket['expire_time'];
		setcookie($name."['id']",$ticket['id'],time()+$expire_time);
		setcookie($name."['create_at']",$ticket['create_at'],time()+$expire_time);
		setcookie($name."['expire_time']",$ticket['expire_time'],time()+$expire_time);
	}

	/*
	 * save ticket and user info in session
	 * @return none
	 */
	private function setSession($ticket,$userInfo)
	{
		$name = $this->_config['ticketName'];
		$_SESSION[$name] = $ticket;
		$userName = $this->_config['userSessionName'];
		$_SESSION[$userName] = $userInfo;
	}

	/*
	 * delete ticket
	 * @return none
	 */
	private function deleteTicket()
	{
		$name = $this->_config['ticketName'];
		unset($_SESSION[$name]);
		setcookie($name,'',time()-3600);
	}

	/*
	 * decrypt user info
	 * @return json string
	 */
	private function decrypt($data)
	{
		$key = md5($this->_config['serverSecret']);
	    $x = 0;
	    $data = base64_decode($data);
	    $len = strlen($data);
	    $l = strlen($key);
	    for ($i = 0; $i < $len; $i++)
	    {
	        if ($x == $l) 
	        {
	            $x = 0;
	        }
	        $char .= substr($key, $x, 1);
	        $x++;
	    }
	    for ($i = 0; $i < $len; $i++)
	    {
	        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
	        {
	            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
	        }
	        else
	        {
	            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
	        }
	    }
	    return $str;
	}

	/*
	 * replace word
	 * @return none
	 */
	private function chanslate_add_to_empty($str)
	{
		// $str = str_replace('=','-',$str);
		// $str = str_replace('+','_',$str);
		$str = str_replace(' ','+',$str);
		return $str;
	}

	/*
	 * ture json to array
	 * @return none
	 */
	private function chanslate_json_to_array($json){
		$json = str_replace('&quot;','"',$json);
		$array = json_decode($json,TRUE);
		return $array;
	}

	/*
	 * check ticket availability
	 * @return bool
	 */
	private function checkTicket($ticketId)
	{
		$signature = $this->signature();
		$url = $this->_config['checkTicketUrl'].$this->signature().'&ticketId='.$ticketId;
		$result = $this->getUrl($url);
		return $result;
	}

	/*
	 * check ticket availability
	 * @return bool
	 */
	private function getUrl($url)
	{
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_HEADER,0);
		// curl_setopt($ch, CURLOPT_ENCODING, "");
		$output = curl_exec($ch);
		curl_close($ch);
	    return $output;
	}

}