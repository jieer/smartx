<?php

namespace SmartX\Models;

use Illuminate\Database\Eloquent\Model;

class UserClub extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartx.database.user_club_table');
    }
}
