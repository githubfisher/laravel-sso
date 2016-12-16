<?php
namespace LaravelSso\Server;

class Server
{
	protected static $_config;

	/*
	 * Constructer
	 * @return none
	 */
	public function __construct($config)
	{
		// check setting of config
		$this->checkConfig($config);
		// set _config
		$this->_config = $config;
	}

	/*
	 * check ticket in session / cookie
	 * @return redirect
	 */
	public function isLogin($request)
	{
		// callback URL
		$callback = $request['callback'];
		$appId = $request['appId'];

		// check Signature
		if(!$this->checkSignature($request)){
			$this->toLogin($callback,$appId);
		}
		$ticket = $this->getTicket();
		if(isset($ticket['id'])){
			if(($ticket['expire_time']+$ticket['create_at']) > time()){
				$this->setTicket(true,$ticket,$appId);
				$this->toClientLogin($callback,$appId);
				return;
			}else{
				// Redirect to LoginView
				$this->deleteTicket();
				$this->toLogin($callback,$appId);
				return;
			}
		}else{
			// Redirect to LoginView
			$this->toLogin($callback,$appId);
			return;
		}
	}

	/*
	 * check client signature
	 * @return bool
	 */
	private function checkSignature($request)
	{
		if($clientInfo = $this->getClientInfo($request['appId'])){
			if($this->getSign($clientInfo['token'],$request['nonce'],$request['timestamp']) == $request['signature'])
				return true;
			return false;
		}else{
			return false;
		}
	}

	/*
	 * make Server signature
	 * @return string
	 */
	private function signature()
	{
		$nonce = $this->getRand();
		$timestamp = (string)time();
		$token = $this->_config['token'];
		// encrypt
		$signature = $this->getSign($nonce,$timestamp,$token);
		return '?signature='.$signature.'&nonce='.$nonce.'&timestamp='.$timestamp;
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
	 * get SESSION['ticket'] or COOKIE['ticket']
	 * @return array or NULL
	 */
	private function getTicket()
	{
		$name = $this->_config['ticketName'];
		$checks = $this->_config['ticketChecks'];
		// get ticket array
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
	 * Rediect to Server Login Page
	 * @return none
	 */
	public function toLogin($callback,$appId)
	{
		// $signature = 
		$loginUrl = $this->_config['loginView'].$this->signature().'&appId='.$appId.'&callback='.$callback;
		header('Location:'.$loginUrl);
	}

	/*
	 * get userInfo
	 * @return bool
	 */
	private function getTicketInfo()
	{
		$ticket = $this->getTicket();
		return '&ticket='.$ticket['id'].'&create_at='.$ticket['create_at'].'&check_at='.$ticket['check_at'].'&expire_time='.$ticket['expire_time'].'&user='.$ticket['user'];
	}

	/*
	 * encrypt userInfo
	 * @return string
	 */
	private function encrypt($data)
	{
		$key = $this->_config['appSecret'];
		// encrypt
		$key    =   md5($key);
	    $x      =   0;
	    $len    =   strlen($data);
	    $l      =   strlen($key);
	    for ($i = 0; $i < $len; $i++)
	    {
	        if ($x == $l) 
	        {
	            $x = 0;
	        }
	        $char .= $key{$x};
	        $x++;
	    }
	    for ($i = 0; $i < $len; $i++)
	    {
	        $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
	    }
	    return base64_encode($str);
	}

	/*
	 * Rediect to Client Login Controller
	 * @return none
	 */
	private function toClientLogin($callback,$appId)
	{
		$ticketInfo = $this->getTicketInfo();
		$clientInfo = $this->getClientInfo($appId);
		$url = $clientInfo['loginUrl'].$this->signature().$ticketInfo.'&callback='.$callback;
		header('Location:'.$url);
	}

	/*
	 * check config
	 * @return bool
	 */
	private function getClientInfo($appId)
	{
		if($this->_config['isUseClientTable']){
			// get app info from client table


		}else{
			// get app info from config array
			return $this->_config['clients'][$appId];
		}
	}

	/*
	 * check config
	 * @return bool
	 */
	private function checkConfig()
	{
		return true;
	}

	/*
	 * login 
	 * @return redirect
	 */
	public function login($userInfo,$callback,$appId)
	{
		if($userInfo){
			$userInfo = $this->encrypt(json_encode($userInfo));
			$ticket = array(
				'id' => $this->getRand(),
				'create_at' => time(),
				'check_at' => time(),
				'expire_time' => $this->_config['expire_time'],
				'user' => $userInfo,
				'clients' => array($appId)
			);
			$this->setTicket(false,$ticket);
			$this->toClientLogin($callback,$appId);
		}else{
			// Login failed !Redirect to LoginView
			$this->toLogin($callback,$appId);
		}
	}

	/*
	 * set ticket 
	 * @return none
	 */
	private function setTicket($type=false,$ticket,$appId)
	{
		$name = $this->_config['ticketName'];

		if($type){
			// add client info to old ticket
			if(!array_search($appId,$ticket['clients']))
				$ticket['clients'][] = $appId;
			$ticket['check_at'] = time();
			setcookie($name,$ticket);
			$_SESSION[$name] = $ticket;
		}else{
			// new ticket
			$expire_time = (int)$ticket['expire_time'];
			setcookie($name."['id']",$ticket['id'],time()+$expire_time);
			setcookie($name."['create_at']",$ticket['create_at'],time()+$expire_time);
			setcookie($name."['expire_time']",$ticket['expire_time'],time()+$expire_time);
			setcookie($name."['check_at']",$ticket['check_at'],time()+$expire_time);
			$i = 0;
			foreach($ticket['clients'] as $client){
				setcookie($name.'[\'clients\']['.$i.']',$client,time()+$expire_time);
				$i++;
			}
			setcookie($name."['user']",$ticket['user']); //debug
			$_SESSION[$name] = $ticket;
		}
	}

	/*
	 * delete ticket from cookie and session
	 * @return none
	 */
	private function deleteTicket()
	{
		$name = $this->_config['ticketName'];
		unset($_SESSION[$name]);
		setcookie($name,'',time()-3600);
	}

	/*
	 * delete cookie and session , redirect to login view
	 * @return none
	 */
	public function logout($request)
	{
		// check Signature
		if(!$this->checkSignature($request)){
			// Redirect to LoginView
			$this->toLogin($callback,$appId);
		}
		$this->deleteTicket();
		// Redirect to LoginView
		$this->toLogin($callback,$appId);
	}
}