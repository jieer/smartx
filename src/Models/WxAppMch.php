<?php

namespace SmartX\Models;

use SmartX\Models\BaseModel;

class WxAppMch extends BaseModel
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartx.database.wx_app_mch_table');
    }

}
