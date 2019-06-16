<?php

namespace App\Model\WebPage;

/**
 * Class WebPageBean
 * Create With Automatic Generator
 * @property int id |
 * @property string webUrl | 网站url
 * @property string pagePath | 页面路径
 * @property int addTime | 插入时间
 * @property int isFlag | isFlag
 * @property string matchValue | matchValue
 * @property int depth | 解析最大层级
 * @property string responseCode |
 * @property int status | 处理状态0待处理,1已处理
 */
class WebPageBean extends \EasySwoole\Spl\SplBean
{
	protected $id;

	protected $webUrl;

	protected $pagePath;

	protected $addTime;

	protected $isFlag;

	protected $matchValue;

	protected $depth;

	protected $responseCode;

	protected $status;


	public function setId($id)
	{
		$this->id = $id;
	}


	public function getId()
	{
		return $this->id;
	}


	public function setWebUrl($webUrl)
	{
		$this->webUrl = $webUrl;
	}


	public function getWebUrl()
	{
		return $this->webUrl;
	}


	public function setPagePath($pagePath)
	{
		$this->pagePath = $pagePath;
	}


	public function getPagePath()
	{
		return $this->pagePath;
	}


	public function setAddTime($addTime)
	{
		$this->addTime = $addTime;
	}


	public function getAddTime()
	{
		return $this->addTime;
	}


	public function setIsFlag($isFlag)
	{
		$this->isFlag = $isFlag;
	}


	public function getIsFlag()
	{
		return $this->isFlag;
	}


	public function setMatchValue($matchValue)
	{
		$this->matchValue = $matchValue;
	}


	public function getMatchValue()
	{
		return $this->matchValue;
	}


	public function setDepth($depth)
	{
		$this->depth = $depth;
	}


	public function getDepth()
	{
		return $this->depth;
	}


	public function setResponseCode($responseCode)
	{
		$this->responseCode = $responseCode;
	}


	public function getResponseCode()
	{
		return $this->responseCode;
	}


	public function setStatus($status)
	{
		$this->status = $status;
	}


	public function getStatus()
	{
		return $this->status;
	}
}

