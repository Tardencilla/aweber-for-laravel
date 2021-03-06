<?php

namespace AWeberForLaravel;

use AWeberForLaravel\Subscriber;

class SubscribersList
{
    protected $awList;
    protected $query;
    protected $queryParams;

    public function __construct(AWeberList $awList)
    {
        $this->awList = $awList;
        $this->query = "all";
        $this->queryParams = [];
    }
    protected function aweber()
    {
        return $this->awList->aweber();
    }
    public function all()
    {
        $this->query = "all";
        $this->queryParams = [];
        return $this;
    }

    public function find($parameters = [])
    {
        $this->query = "find";
        $this->queryParams = $parameters;
        return $this;
    }

    public function tag($add = [], $remove = [])
    {
    }

    public function fetch(callable $fetching, callable $onFinish)
    {
        switch ($this->query) {
            case 'all':
                return $this->_getAll($fetching, $onFinish);
                break;
            case 'find':
                return $this->_find($fetching, $onFinish);
                break;
        }
    }

    protected function _getAll(callable $onUpdate = null, callable $onFinish = null)
    {
        $ret = [];
        $url = sprintf("https://api.aweber.com/1.0/accounts/%s/lists/%s/subscribers", $this->aweber()->accountId(), $this->awList->id());
        $offset = 0;
        do {
            $subRet = [];
            $options = [
            'ws.start' => $offset,
            'ws.size'  => 100
            ];
            $body = $this->aweber()->request('GET', $url, $options);
            $cnt = count($body['entries']);
            //$ret = array_merge($ret, $body['entries']);
            foreach ($body['entries'] as $entry) {
                $s = new Subscriber($entry);
                $subRet[] = $s;
                $ret[] = $s;
            }

            $offset += $cnt;
            if ($onUpdate != null) {
                $onUpdate($subRet);
            }
            sleep(1);
        } while ($cnt==100);
        if ($onFinish != null) {
            $onFinish($ret);
        }
        return $ret;
    }

    protected function _find(callable $fetching, callable $onFinish)
    {
        return [];
    }
}
