<?php

namespace Modules\Opx\Menu\Models;

use Core\Traits\Model\Publishing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use SoftDeletes,
        Publishing;
	
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'publish_start', 'publish_end'];

	public function children()
	{
		return $this->hasMany(__CLASS__, 'parent_id');
	}
}
