<?php

namespace SmartX\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class BaseModel extends Model
{
    protected function serializeDate(DateTimeInterface $date) :string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
