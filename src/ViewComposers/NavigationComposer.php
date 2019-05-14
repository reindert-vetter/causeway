<?php

namespace Exdeliver\Causeway\ViewComposers;

use Exdeliver\Causeway\Domain\Entities\Menu\Menu;
use Illuminate\Support\Facades\Log;

/**
 * Class NavigationComposer
 * @package App\Http\ViewComposers
 */
class NavigationComposer
{
    /**
     * @param $view
     */
    public function compose($view)
    {
        try {
            $siteMenu = \CW::getMenu('site-menu');
        } catch (\Exception $e) {
            Log::error('No site-menu found.');
            $siteMenu = null;
        }

        $view->with('site_menu', $siteMenu);
    }
}
