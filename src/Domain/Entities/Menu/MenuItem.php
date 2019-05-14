<?php

namespace Exdeliver\Causeway\Domain\Entities\Menu;

use Dimsav\Translatable\Translatable;
use Exdeliver\Causeway\Domain\Common\Entity;
use Exdeliver\Causeway\Domain\Common\Interfaces\MenuItemInterface;

/**
 * Class MenuItem
 * @package Domain\Entities\Menu
 */
class MenuItem extends Entity implements MenuItemInterface
{
    use Translatable;

    /**
     * @var array
     */
    public $translatedAttributes = ['label', 'url'];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')
            ->orderBy('sequence', 'desc');
    }
}
