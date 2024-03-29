<?php

namespace SmartX\Models;

use Illuminate\Database\Eloquent\Model;

class VerifyCode extends Model
{
    protected $table;
    protected $fillable = ['ip', 'phone', 'code', 'action', 'ttl', 'usable', 'strategy'];
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartx.database.verify_code_table');
    }
}
