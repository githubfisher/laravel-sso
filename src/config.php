<?php
return array(
	//'配置项'=>'配置值'
	/*
	 * Server Info
	 */
	'appId' => '1000000',
	'appSecret' => '1000001',
	'token' => '123456',

	/*
	 *  Server check login URL
	 */
	'isLogin' => 'http://tpsso/index.php/Home/server/isLogin',

	/*
	 * Server LoginView URL
	 */
	'loginView' => 'http://tpsso/index.php/Home/server/LoginView',

	/*
	 * Server Logout URL
	 */
	'loginView' => 'http://tpsso/index.php/Home/server/index',

	/*
	 * Server Login Controller
	 */
	'loginController' => 'http://tpsso/index.php/Home/server/Login',

	/*
	 * Users Table Name
	 */
	'userTable' => 'user',

	/*
	 * is Use Ticket Table
	 */
	'isUseClientTable' => false,

	/*
	 *  Ticket Table Name
	 */
	'clientTable' => 'client',

	/*
	 * Ticket Checks
	 */
	'ticketChecks' => 'all', // session , cookie

	/*
	 * Ticket Store Name
	 * ticket = ['id'=>'Rand()','create_at'=>'timestamp','expire_time'=>'6000','user'=>'encrypted','clients'=>['client1','client2']]
	 */
	'ticketName' => 'ticket',

	/*
	 * Ticke Store Expire Time , second
	 */
	'expire_time' => 6000,

	/*
	 * Client Application Info
	 */
	'clients' => array(
		'9001' => array(
			'appId' => '9001',
			'appSecret' => '9000',
			'token' => '654321',
			'loginUrl' => 'http://localhost/index.php/admin/SsoClient/login',
			'logoutUrl' => 'http://localhost/index.php/admin/SsoClient/toLogout'
		),
		'9002' => array(
			'appId' => '9002',
			'appSecret' => '2000002',
			'token' => '87654',
			'loginUrl' => 'http://qm2/index.php/admin/SsoClient/login',
			'logoutUrl' => 'http://qm2/index.php/admin/SsoClient/toLogout'
		)
	)
);