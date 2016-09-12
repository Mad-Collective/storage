<?php

namespace Cmp\Storage;

use Cmp\Storage\Exception\InvalidPathException;
use Cmp\Storage\Exception\RelativePathNotAllowed;

/**
 * Class VirtualPath.
 */
class VirtualPath
{
    /**
     * @var string
     */
    private $path;

    /**
     * VirtualPath constructor.
     *
     * @param $path
     */
    public function __construct($path)
    {

        $this->path = $this->makePathAbsolute($path);
        $this->path = $this->canonicalize($this->path);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     * @param $path
     *
     * @return string
     */
    public function makePathAbsolute($path)
    {
        if (!$this->isAbsolutePath($path)) {
            return DIRECTORY_SEPARATOR.join(DIRECTORY_SEPARATOR, [trim(getcwd(), DIRECTORY_SEPARATOR), $path]);
        }

        return $path;

    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function isAbsolutePath($path)
    {
        if (empty($path) || !is_string($path)) {
            return false;
        }

        if (!($path[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $path) > 0)) {
            return false;
        }

        return true;
    }

    /**
     * @param $path
     *
     * @return string
     */
    private function canonicalize($path)
    {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    /**
     * @param VirtualPath $path
     *
     * @return bool
     */
    public function isChild(VirtualPath $path)
    {
        if (strcmp($this->getPath(), $path->getPath()) == 0) {
            return false;
        }

        return strpos($path->getPath(), $this->getPath()) === 0;
    }
}
