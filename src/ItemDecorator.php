<?php

namespace KodiCMS\Navigation;

use Illuminate\Support\Str;
use KodiCMS\Navigation\Contracts\NavigationSectionInterface;

/**
 * Class ItemDecorator.
 *
 * @method setIcon($icon)
 */
class ItemDecorator
{
    /**
     * @var array
     */
    protected $attributes = [
        'permissions' => null,
    ];

    /**
     * @var Section
     */
    protected $sectionObject;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->setAttribute($data);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getAttribute('status', false);
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return (bool) $this->getAttribute('hidden', false);
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        if (!isset($this->icon)) {
            return;
        }

        return '<i class="fa fa-'.e($this->icon).' menu-icon"></i>';
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->getAttribute('name');
    }

    /**
     * @return string
     */
    public function getName()
    {
        $label = $this->getLabel();

        return is_null($label) ? $this->getAttribute('name') : $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if (!is_null($label = $this->getAttribute('label'))) {
            return trans($label);
        }

        return;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->getAttribute('url');
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return (array) $this->getAttribute('premissions');
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return (int) $this->getAttribute('priority');
    }

    /**
     * @param int $priority
     *
     * @return int
     */
    public function setPriority($priority)
    {
        return (int) $priority;
    }

    /**
     * @param bool $status
     *
     * @return $this
     */
    public function setStatus($status = true)
    {
        if ($this->getRootSection() instanceof Section) {
            $this->getRootSection()->setStatus((bool) $status);
        }

        return (bool) $status;
    }

    /**
     * @param NavigationSectionInterface $section
     *
     * @return $this
     */
    public function setRootSection(NavigationSectionInterface &$section)
    {
        $this->sectionObject = $section;

        return $this;
    }

    /**
     * @return Section
     */
    public function getRootSection()
    {
        return $this->sectionObject;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed|null
     */
    public function getAttribute($name, $default = null)
    {
        return array_get($this->attributes, $name, $default);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setAttribute($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->setAttribute($key, $value);
            }
        } else {
            $method = 'set'.ucfirst($name);
            if (method_exists($this, 'set'.ucfirst($name))) {
                $this->attributes[$name] = $this->{$method}($value);
            } else {
                $this->attributes[$name] = $value;
            }
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        unset($this->attributes[$name]);
    }

    /**
     * is triggered when invoking inaccessible methods in an object context.
     *
     * @param $name      string
     * @param $arguments array
     *
     * @return mixed
     *
     * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
     */
    public function __call($name, $arguments)
    {
        if (Str::startsWith($name, 'set') and count($arguments) === 1) {
            $method = substr($name, 3);

            return $this->setAttribute(strtolower($method), $arguments[0]);
        }
    }
}
