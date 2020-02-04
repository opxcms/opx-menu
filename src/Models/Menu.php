<?php /** @noinspection ALL */

/** @noinspection PhpUnused */

namespace Modules\Opx\Menu\Models;

use Core\Traits\Model\Publishing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Menu
 * @package Modules\Opx\Menu\Models
 * @method static Builder withTrashed()
 */
class Menu extends Model
{
    use SoftDeletes,
        Publishing;

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function items()
    {
        /** @var Builder $query */
        $query = $this->hasMany(MenuItem::class);

        return self::addPublishingToQuery($query)->orderBy('parent_id');
    }
}
