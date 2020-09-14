<?php

namespace SmartX\Models;

use Illuminate\Database\Eloquent\Model;

class Id extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartx.database.user_id_table');
    }

    protected function getID($phone) {
        if (empty($phone) || empty(preg_match('/^1[0-9]{10}$/', $phone))) {
            return 0;
        }
        $obj = self::where('phone', $phone)->first();
        if ($obj) {
            return $obj->id;
        }
        $obj = new ID();
        $obj->phone = $phone;
        $obj->save();
        return $obj->id;
    }
}
