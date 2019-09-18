<?php

namespace Modules\Opx\Menu\Models;

use Core\Traits\Model\Publishing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes,
        Publishing;

	protected $dates = ['created_at', 'updated_at', 'deleted_at'];

	public function items()
	{
		return self::addPublishingToQuery($this->hasMany(MenuItem::class))->orderBy('parent_id');
	}
}
