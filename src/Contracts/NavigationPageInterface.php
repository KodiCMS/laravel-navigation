<?php

namespace KodiCMS\Navigation\Contracts;

interface NavigationPageInterface
{
    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return string
     */
    public function getIcon();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return array
     */
    public function getPermissions();

    /**
     * @param bool $status
     */
    public function setStatus($status = true);

    /**
     * @param NavigationSectionInterface $section
     *
     * @return $this
     */
    public function setRootSection(NavigationSectionInterface &$section);

    /**
     * @return Section
     */
    public function getRootSection();
}
