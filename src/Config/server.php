<?php
return [
	/*
	 * Server Info
	 */
	'appId' => '1000000',
	'appSecret' => '1000001',
	'token' => '123456',

	/*
	 *  Server check login URL
	 */
	'isLogin' => 'http://192.168.0.18/index.php/Home/server/isLogin',

	/*
	 * Server LoginView URL
	 */
	'loginView' => 'http://192.168.0.18/index.php/Home/server/LoginView',

	/*
	 * Server Logout URL
	 */
	'loginView' => 'http://192.168.0.18/index.php/Home/server/Logout',

	/*
	 * Server Login Controller
	 */
	'loginController' => 'http://192.168.0.18/index.php/Home/server/Login',

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
	'clients' => [
		'2000001' => [
			'appId' => '9001',
			'appSecret' => '9000',
			'token' => '654321',
			'loginUrl' => '',
			'logoutUrl' => ''
		],
		'2000002' => [
			'appId' => '2000002',
			'appSecret' => '2000002',
			'token' => '654321',
			'loginUrl' => '',
			'logoutUrl' => ''
		]
	]
];