<?php

namespace App\Model\Domain;

/**
 * Class DomainBean
 * Create With Automatic Generator
 * @property int id |
 * @property string url |
 * @property string domain |
 * @property int state |
 */
class DomainBean extends \EasySwoole\Spl\SplBean
{
	protected $id;

	protected $url;

	protected $domain;

	protected $state;


	public function setId($id)
	{
		$this->id = $id;
	}


	public function getId()
	{
		return $this->id;
	}


	public function setUrl($url)
	{
		$this->url = $url;
	}


	public function getUrl()
	{
		return $this->url;
	}


	public function setDomain($domain)
	{
		$this->domain = $domain;
	}


	public function getDomain()
	{
		return $this->domain;
	}


	public function setState($state)
	{
		$this->state = $state;
	}


	public function getState()
	{
		return $this->state;
	}
}

