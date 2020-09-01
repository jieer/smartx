<?php
/**
 * Created by PhpStorm.
 * User: smartwell
 * Date: 2018/12/13
 * Time: 下午2:07
 */

namespace Smartwell\Models;

use Illuminate\Database\Eloquent\Model;

class WxApp extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartwell.database.wx_app_table');
    }
}
