<?php
/**
 * Verify Home Route
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Controller;

use CommonApi\Route\RouteInterface;

/**
 * Verify Home Route
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
abstract class Home extends Base implements RouteInterface
{
    /**
     * Determine if request is for home page
     *
     * @return  object
     * @since   1.0.0
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
     * @since   1.0.0
     */
    protected function verifyHomeEmptyPath()
    {
        if (strlen($this->application_path) === 0
            || $this->application_path === ''
        ) {
            return $this->setHome();
        }

        return false;
    }

    /**
     * Home: slash (redirect)
     *
     * @return  boolean
     * @since   1.0.0
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
     * @since   1.0.0
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
     * Home: Set Catalog ID
     *
     * @return  boolean
     * @since   1.0.0
     */
    protected function setHome()
    {
        return $this->setRouteValues($this->home_route);
    }

    /**
     * Home: Set Redirect
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setHomeRedirect()
    {
        $this->route->error_code      = 404;
        $this->route->redirect_to_url = $this->base_url . $this->home_route->sef_request;

        return $this;
    }
}
