<?php

namespace SmartX\Models;

use SmartX\Models\BaseModel;

class UserClub extends BaseModel
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartx.database.user_club_table');
    }
}
