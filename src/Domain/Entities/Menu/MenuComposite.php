<?php

namespace Exdeliver\Causeway\Domain\Entities\Menu;

use Exdeliver\Causeway\Domain\Common\Interfaces\RenderableInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\View;

/**
 * Class MenuComposite
 * @package Exdeliver\Causeway\Domain\Entities\Menu
 */
class MenuComposite implements RenderableInterface
{
    /**
     * @var RenderableInterface
     */
    protected $items;

    /**
     * @var
     */
    protected $template;

    /**
     * @return View
     */
    public function render(): string
    {
        $items = collect($this->items)
            ->filter(function ($item) {
                return $item->parent_id === null;
            })->map(function ($item) {
                $item = new MenuElementComposite($item);
                if ($this->template !== null) {
                    $item->setTemplate($this->template);
                }
                return $item;
            });

        if (view()->exists('site::menu._' . $this->template)) {
            return view('site::menu._' . $this->template, [
                'items' => $items,
            ]);
        }

        return view('site::menu._default', [
            'items' => $items,
        ]);
    }

    /**
     * @param Arrayable $item
     */
    public function addItem(Arrayable $item)
    {
        $this->items[] = $item;
    }

    /**
     * @param string|null $template
     * @return $this
     */
    public function setTemplate(string $template = null)
    {
        $this->template = $template;
        return $this;
    }
}