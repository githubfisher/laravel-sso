<?php
namespace LaravelSso\Server;

class Server
{
	private static $instance;

	private function __construct($config)
	{

	}

	public function Signature($config)
	{
		return json_encode($config);
	}

	public function checkSignature()
	{

	}
}