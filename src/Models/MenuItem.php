<?php

namespace Modules\Opx\Menu\Models;

use Core\Traits\Model\Publishing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MenuItem
 * @package Modules\Opx\Menu\Models
 * @method static Builder withTrashed()
 */
class MenuItem extends Model
{
    use SoftDeletes,
        Publishing;

    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'publish_start', 'publish_end'];

    /**
     * Children nodes of menu item.
     *
     * @return  HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(__CLASS__, 'parent_id');
    }
}
