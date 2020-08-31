<?php

namespace Smartwell\Models;

trait BaseApiModelTrait
{
    public function formatList($data) {
        return $data;
    }

    public function formatResult($result) {
        return $result;
    }

    public function apiList($page, $rows, $sidx, $sord, $filters = []) {
        $result =  static::where('id', '>', 0);
        if (count($filters) > 0) {
            foreach ($filters as $filter) {
                if (empty($filter['index']) || !isset($filter['value'])) {
                    continue;
                }
                if ($filter['type'] == 'like') {
                    $result = $result->where($filter['index'], 'like', '%' . $filter['value'] . '%');
                } else {
                    $result = $result->where($filter['index'], $filter['value']);
                }
            }
        }
        $result = $result->orderBy($sidx, $sord)->paginate($rows)->toArray();

        $format_data = [];
        $return_data = [];
        $datas = $result['data'];
        foreach ($datas as $index=>$data) {
            $data = $this->formatList($data);
            if (empty($data)) {
                continue;
            }
            array_push($format_data, $data);
        }
        $return_data['data'] = $format_data;
        $return_data['records'] = $result['total'];
        $return_data['total'] = $result['last_page'];

        $return_data['page'] = $page;
        $return_data['rows'] = $rows;
        $return_data['sidx'] = $sidx;
        $return_data['sord'] = $sord;
        $return_data['filters'] = $filters;

        return $this->formatResult($return_data);
    }

    public function getSelectList() {
        $modules = config('smartwell.models.' . lcfirst(strtolower(basename(str_replace('\\', '/',  __CLASS__)))) . '.modules');
        if (empty($modules) || count($modules) < 1) {
            return static::all();
        } else {
            return static ::all($modules);
        }
    }

    public function doEdit($params) {
        if (empty($params) || count($params) < 1) {
            return false;
        }
        if (empty($params['id'])) {
            $class = new static();
        } else {
            $class = static::find($params['id']);
            if (empty($class)) {
                return false;
            }
        }
        foreach ($params as $key=>$value) {
            if ($value == '') {
                continue;
            }
            $class->$key = $value;
        }

        $class->save();
        return true;

    }


}
