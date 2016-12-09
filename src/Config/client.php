<?php
return [
	/*
	 * Client Info
	 */
	'appId' => '9001',
	'appSecret' => '9000',
	'token' => '654321',

	/*
	 * Server Info
	 */
	'serverId' => '1000000',
	'serverSecret' => '1000001',
	'serverToken' => '123456',

	/*
	 *  Server check login URL
	 */
	'isLogin' => 'http://192.168.0.18/index.php/Home/server/isLogin',

	/*
	 * Server Login URL
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
	 * Ticket Checks
	 */
	'ticketChecks' => 'all', // session , cookie

	/*
	 * Ticket Store Name
	 * ticket = ['id'=>'Rand()','create_at'=>'timestamp','expire_time'=>'6000','user'=>'encrypted','clients'=>['client1','client2']]
	 */
	'ticketName' => 'ticket',

	/*
	 * user Session Name
	 */
	'userSessionName' => 'user',
];