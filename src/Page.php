<?php

namespace KodiCMS\Navigation;

use KodiCMS\Navigation\Contracts\NavigationPageInterface;

class Page extends ItemDecorator implements NavigationPageInterface
{
    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function __set($name, $value)
    {
        parent::__set($name, $value);

        if (!is_null($this->sectionObject)) {
            $this->getRootSection()->update();
        }

        return $this;
    }
}
