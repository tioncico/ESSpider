<?php

namespace App\HttpController;

/**
 * BaseController
 * Class Base
 * Create With Automatic Generator
 */
abstract class Base extends \EasySwoole\Http\AbstractInterface\Controller
{
	public function index()
	{
		$this->actionNotFound('index');
	}
}

