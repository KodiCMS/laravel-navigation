<?php

namespace KodiCMS\Navigation;

use Event;
use Request;

class Navigation
{
    /**
     * @param array  $items
     * @param string $uri
     *
     * @return Navigation
     */
    public static function make(array $items, $uri = null)
    {
        return new static($items, $uri);
    }

    /**
     * @var Section
     */
    protected $rootSection;

    /**
     * @var Page
     */
    protected $currentPage;

    /**
     * @var string
     */
    protected $uri = '';

    /**
     * Navigation constructor.
     *
     * @param array       $items
     * @param string|null $uri
     */
    public function __construct(array $items, $uri = null)
    {
        if (is_null($uri)) {
            $uri = Request::getUri();
        }

        $this->uri = $uri;

        $this->build($items);

        Event::fire('navigation.inited', [$this]);

        $this->getRootSection()->findActivePageByUri(strtolower($uri));
        $this->getRootSection()->sort();
    }

    /**
     * @return Section
     */
    public function getRootSection()
    {
        if (is_null($this->rootSection)) {
            $this->createRootSection();
        }

        return $this->rootSection;
    }

    /**
     * @param string       $name
     * @param Section|null $parent
     * @param int          $priority
     *
     * @return Section
     */
    public function addSection($name, Section $parent = null, $priority = 1)
    {
        if (is_null($parent)) {
            $parent = $this->getRootSection();
        }

        $section = new Section($this, [
            'name'     => $name,
            'priority' => $priority,
        ]);

        $parent->addPage($section);

        return $section;
    }

    /**
     * @param string  $name
     * @param Section $parent
     *
     * @return Section
     */
    public function findSection($name, Section $parent = null)
    {
        if (is_null($parent)) {
            $parent = $this->getRootSection();
        }

        return $parent->findSection($name);
    }

    /**
     * @param string  $name
     * @param Section $parent
     * @param int     $priority
     *
     * @return Section
     */
    public function findSectionOrCreate($name, Section $parent = null, $priority = 1)
    {
        if (is_null($parent)) {
            $parent = $this->getRootSection();
        }

        $section = $this->findSection($name, $parent);

        if (is_null($section)) {
            $section = $this->addSection($name, $parent, $priority);
        }

        return $section;
    }

    /**
     * @param string $section
     * @param string $name
     * @param string $uri
     * @param int    $priority
     *
     * @return $this
     */
    public function addPageToSection($section = 'Other', $name, $uri, $priority = 0)
    {
        return $this->findSectionOrCreate($section)->addPage(new Page([
            'name' => $name,
            'url'  => $uri,
        ]), $priority);
    }

    /**
     * @param string $uri
     * @param array  $data
     */
    public function update($uri, array $data)
    {
        $page = $this->findPageByUri($uri);

        if ($page instanceof Page) {
            foreach ($data as $key => $value) {
                $page->{$key} = $value;
            }
        }
    }

    /**
     * @param string $uri
     *
     * @return null|Page
     */
    public function &findPageByUri($uri)
    {
        foreach ($this->getRootSection()->getSections() as $section) {
            if ($page = $section->findPageByUri($uri)) {
                return $page;
            }
        }

        return;
    }

    /**
     * @return Page
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param Page $page
     */
    public function setCurrentPage(Page &$page)
    {
        $this->currentPage = $page;
    }

    /**
     * @param array $items
     */
    protected function build(array $items)
    {
        foreach ($items as $section) {
            if (! isset($section['name'])) {
                continue;
            }

            if (isset($section['url'])) {
                $sectionObject = $this->getRootSection();

                $page = new Page($section);
                $sectionObject->addPage($page);
            } else {
                $sectionObject = $this->findSectionOrCreate($section['name']);
                $sectionObject->setAttribute(array_except($section, ['children']));

                if (! empty($section['children'])) {
                    $sectionObject->addPages($section['children']);
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function sort()
    {
        uasort($this->getRootSection()->getSections(), function (Section $a, Section $b) {
            if ($a->getId() == $b->getId()) {
                return 0;
            }

            return ($a->getId() < $b->getId()) ? -1 : 1;
        });
    }

    /**
     * @return void
     */
    protected function createRootSection()
    {
        $this->rootSection = new Section($this, [
            'name' => Section::ROOT_NAME,
        ]);
    }
}
