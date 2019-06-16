<?php

namespace App\Model\WebPage;

/**
 * Class WebPageModel
 * Create With Automatic Generator
 */
class WebPageModel extends \App\Model\BaseModel
{
	protected $table = 'web_page_list';

	protected $primaryKey = 'id';


	/**
	 * @getAll
	 * @param  int  page  1
	 * @param  string  keyword
	 * @param  int  pageSize  10
	 * @return array[total,list]
	 */
	public function getAll(int $page = 1, string $keyword = null, int $pageSize = 10): array
	{
		if (!empty($keyword)) {
		    $this->getDb()->where('', '%' . $keyword . '%', 'like');
		}

		$list = $this->getDb()
		    ->withTotalCount()
		    ->orderBy($this->primaryKey, 'DESC')
		    ->get($this->table, [$pageSize * ($page  - 1), $pageSize]);
		$total = $this->getDb()->getTotalCount();
		return ['total' => $total, 'list' => $list];
	}


	/**
	 * 默认根据主键(id)进行搜索
	 * @getOne
	 * @param  WebPageBean $bean
	 * @return WebPageBean
	 */
	public function getOne(WebPageBean $bean): ?WebPageBean
	{
		$info = $this->getDb()->where($this->primaryKey, $bean->getId())->getOne($this->table);
		if (empty($info)) {
		    return null;
		}
		return new WebPageBean($info);
	}


	/**
	 * 默认根据bean数据进行插入数据
	 * @add
	 * @param  WebPageBean $bean
	 * @return bool
	 */
	public function add(WebPageBean $bean): bool
	{
		return $this->getDb()->insert($this->table, $bean->toArray(null, $bean::FILTER_NOT_NULL));
	}


	/**
	 * 默认根据主键(id)进行删除
	 * @delete
	 * @param  WebPageBean $bean
	 * @return bool
	 */
	public function delete(WebPageBean $bean): bool
	{
		return  $this->getDb()->where($this->primaryKey, $bean->getId())->delete($this->table);
	}


	/**
	 * 默认根据主键(id)进行更新
	 * @delete
	 * @param  WebPageBean $bean
	 * @param  array $data
	 * @return bool
	 */
	public function update(WebPageBean $bean, array $data): bool
	{
		if (empty($data)){
		    return false;
		}
		return $this->getDb()->where($this->primaryKey, $bean->getId())->update($this->table, $data);
	}

	function deleteByWebUrl($webUrl){
        return  $this->getDb()->where('webUrl', $webUrl)->delete($this->table);
    }

    function getOneByPageUrl($pageUrl){

        $info = $this->getDb()->where('pagePath', $pageUrl)->getOne($this->table);
        if (empty($info)) {
            return null;
        }
        return new WebPageBean($info);
    }
}

