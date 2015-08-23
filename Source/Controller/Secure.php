<?php
/**
 * Verify Secure Route
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Controller;

use CommonApi\Route\RouteInterface;

/**
 * Verify Secure Route
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
abstract class Secure extends Home implements RouteInterface
{
    /**
     * Determine if secure protocol required and in use
     *
     * @return  object
     * @since   1.0.0
     */
    public function verifySecureProtocol()
    {
        if ((int)$this->url_force_ssl === 0) {
            return $this->route;
        }

        if ((int)$this->request->secure === 1) {
            return $this->route;
        }

        $this->setHomeRedirect();

        return $this->route;
    }
}
