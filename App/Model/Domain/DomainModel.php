<?php

namespace App\Model\Domain;

/**
 * Class DomainModel
 * Create With Automatic Generator
 */
class DomainModel extends \App\Model\BaseModel
{
    protected $table = 'domain_list';

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
            ->get($this->table, [$pageSize * ($page - 1), $pageSize]);
        $total = $this->getDb()->getTotalCount();
        return ['total' => $total, 'list' => $list];
    }


    /**
     * 默认根据主键(id)进行搜索
     * @getOne
     * @param  DomainBean $bean
     * @return DomainBean
     */
    public function getOne(DomainBean $bean): ?DomainBean
    {
        $info = $this->getDb()->where($this->primaryKey, $bean->getId())->getOne($this->table);
        if (empty($info)) {
            return null;
        }
        return new DomainBean($info);
    }


    public function getOneByDomain($domain): ?DomainBean
    {
        $info = $this->getDb()->where('domain', $domain)->getOne($this->table);
        if (empty($info)) {
            return null;
        }
        return new DomainBean($info);
    }


    /**
     * 默认根据bean数据进行插入数据
     * @add
     * @param  DomainBean $bean
     * @return bool
     */
    public function add(DomainBean $bean): bool
    {
        return $this->getDb()->insert($this->table, $bean->toArray(null, $bean::FILTER_NOT_NULL));
    }


    /**
     * 默认根据主键(id)进行删除
     * @delete
     * @param  DomainBean $bean
     * @return bool
     */
    public function delete(DomainBean $bean): bool
    {
        return $this->getDb()->where($this->primaryKey, $bean->getId())->delete($this->table);
    }


    /**
     * 默认根据主键(id)进行更新
     * @delete
     * @param  DomainBean $bean
     * @param  array      $data
     * @return bool
     */
    public function update(DomainBean $bean, array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        return $this->getDb()->where($this->primaryKey, $bean->getId())->update($this->table, $data);
    }
}

