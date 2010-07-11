<?php
/**
 * @namespace Gitty\Config
 */
namespace Gitty\Config;

/**
 * interface for config loaders
 */
interface Loader
{
    /**
     * return data as array
     */
    public function toArray();
}