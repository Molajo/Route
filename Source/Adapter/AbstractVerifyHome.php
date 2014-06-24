<?php
/**
 * Verify Home Route
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Adapter;

use CommonApi\Route\RouteInterface;

/**
 * Verify Home Route
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
abstract class AbstractVerifyHome extends AbstractAdapter implements RouteInterface
{
    /**
     * Determine if request is for home page
     *
     * @return  object
     * @since   1.0
     */
    public function verifyHome()
    {
        if ($this->verifyHomeEmptyPath() === true) {
            return $this->route;
        }

        if ($this->verifyHomeSlash() === true) {
            return $this->route;
        }

        if ($this->verifyHomeIndex() === true) {
            return $this->route;
        }

        return $this->route;
    }

    /**
     * Home: application path
     *
     * @return  boolean
     * @since   1.0
     */
    protected function verifyHomeEmptyPath()
    {
        if (strlen($this->application_path) === 0
            || $this->application_path === ''
        ) {
            return $this->setHomeCatalog();
        }

        return false;
    }

    /**
     * Home: slash (redirect)
     *
     * @return  boolean
     * @since   1.0
     */
    protected function verifyHomeSlash()
    {
        if ($this->application_path === '/') {
            $this->setHomeRedirect();
            return true;
        }

        return false;
    }

    /**
     * Home: index.php (redirect)
     *
     * @return  boolean
     * @since   1.0
     */
    public function verifyHomeIndex()
    {
        if (in_array($this->application_path, array('index.php', 'index.php/', 'index.php?', '/index.php/'))) {
            $this->setHomeRedirect();

            return true;
        }

        return false;
    }

    /**
     * Home: set redirect
     *
     * @return  boolean
     * @since   1.0
     */
    protected function setHomeCatalog()
    {
        $this->route->catalog_id = $this->application_home_catalog_id;
        $this->route->home       = 1;

        return true;
    }

    /**
     * Home: set redirect
     *
     * @return  $this
     * @since   1.0
     */
    protected function setHomeRedirect()
    {
        $this->route->error_code      = 301;
        $this->route->redirect_to_url = $this->application_home_catalog_id;

        return $this;
    }
}
