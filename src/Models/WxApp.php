<?php

namespace SmartX\Models;

use Illuminate\Database\Eloquent\Model;

class WxApp extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartx.database.wx_app_table');
    }

    public static function getDefault()
    {
        return (object)null;
    }
}
