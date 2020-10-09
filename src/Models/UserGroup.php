<?php

namespace SmartX\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartx.database.user_group_table');
    }
}
