<?php

namespace SmartX\Models;

use SmartX\Models\BaseModel;

class WxApp extends BaseModel
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartx.database.wx_app_table');
    }

    public static function getDefault()
    {
        $app = self::where('is_default', 1)->first();
        if (empty($app)) {
            return self::find(1);
        }
        if (empty($app)) {
            return null;
        }
        return $app;
    }
}
