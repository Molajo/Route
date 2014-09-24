<?php
/**
 * Route Test
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route;

use stdClass;
use Molajo\Query\MockReadController;
use Molajo\Route\Adapter\Database;
use Molajo\Route\Driver;

/**
 * Route Test
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Object
     */
    protected $route;

    /**
     * Get the Route Adapter Handler
     *
     * @covers  Molajo\Route\Driver::__construct
     * @covers  Molajo\Route\Driver::verifySecureProtocol
     * @covers  Molajo\Route\Driver::verifyHome
     * @covers  Molajo\Route\Driver::setRequest
     * @covers  Molajo\Route\Driver::setRoute
     * @covers  Molajo\Route\Adapter\Database::__construct
     * @covers  Molajo\Route\Adapter\Database::setRoute
     * @covers  Molajo\Route\Adapter\AbstractRequest::setRequest
     * @covers  Molajo\Route\Adapter\AbstractRequest::setAction
     * @covers  Molajo\Route\Adapter\AbstractRequest::setBaseUrl
     * @covers  Molajo\Route\Adapter\AbstractRequest::setRequestVariables
     * @covers  Molajo\Route\Adapter\AbstractRequest::setParameters
     * @covers  Molajo\Route\Adapter\AbstractRequest::removePathSlash
     * @covers  Molajo\Route\Adapter\AbstractRequest::getParameters
     * @covers  Molajo\Route\Adapter\AbstractRequest::getParameterPairs
     * @covers  Molajo\Route\Adapter\AbstractRequest::getPath
     * @covers  Molajo\Route\Adapter\AbstractRequest::getNode
     * @covers  Molajo\Route\Adapter\AbstractRequest::setIndexAtMax
     * @covers  Molajo\Route\Adapter\AbstractRequest::decrementIndex
     * @covers  Molajo\Route\Adapter\AbstractRequest::parseParameterPair
     * @covers  Molajo\Route\Adapter\AbstractRequest::setPageType
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHome
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHomeEmptyPath
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHomeSlash
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHomeIndex
     * @covers  Molajo\Route\Adapter\AbstractAdapter::initialiseRoute
     * @covers  Molajo\Route\Adapter\AbstractAdapter::verifySecureProtocol
     *
     */
    protected function getAdapter(
        $url_force_ssl = 0,
        $is_secure = 1,
        $application_path = 'articles'
    ) {
        $request               = new stdClass();
        $request->method       = 'GET';
        $request->content_type = "text/html";
        $request->base_url     = "http://site2/";
        $request->url          = "http://site2/admin/articles";
        $request->scheme       = "http://";
        $request->secure       = $is_secure;
        $request->user         = '';
        $request->password     = '';
        $request->userinfo     = '';
        $request->host         = "site2";
        $request->port         = "80";
        $request->authority    = "site2/";
        $request->path         = "admin/articles";
        $request->query        = "";
        $request->parameters   = array();

        $request->url_force_ssl               = $url_force_ssl;
        $request->application_home_catalog_id = 1072;
        $request->application_path            = $application_path;
        $request->application_id              = 2;
        $request->base_url                    = 'http://site2/admin/';

        $filters   = array();
        $filters[] = 'author';
        $filters[] = 'category';
        $filters[] = 'tag';
        $filters[] = 'date';
        $filters[] = 'theme';
        $filters[] = 'page';
        $filters[] = 'edit';
        $filters[] = 'delete';

        $task_to_action              = array();
        $task_to_action['order']     = 'view';
        $task_to_action['publish']   = 'update';
        $task_to_action['unpublish'] = 'update';
        $task_to_action['delete']    = 'delete';

        $page_types = array(
            'new'    => 'new',
            'edit'   => 'edit',
            'delete' => 'delete'
        );

        $resource_query = new MockReadController();

        return new Database(
            $request,
            $filters,
            $task_to_action,
            $page_types,
            $resource_query
        );
    }

    /**
     * Initialises Adapter
     */
    protected function getRouteDriver($adapter)
    {
        return new Driver($adapter);
    }

    /**
     * verifySecureProtocol -- Not SSL
     *
     * @covers  Molajo\Route\Driver::__construct
     * @covers  Molajo\Route\Driver::verifySecureProtocol
     * @covers  Molajo\Route\Driver::verifyHome
     * @covers  Molajo\Route\Driver::setRequest
     * @covers  Molajo\Route\Driver::setRoute
     * @covers  Molajo\Route\Adapter\Database::__construct
     * @covers  Molajo\Route\Adapter\Database::setRoute
     * @covers  Molajo\Route\Adapter\AbstractRequest::setRequest
     * @covers  Molajo\Route\Adapter\AbstractRequest::setAction
     * @covers  Molajo\Route\Adapter\AbstractRequest::setBaseUrl
     * @covers  Molajo\Route\Adapter\AbstractRequest::setRequestVariables
     * @covers  Molajo\Route\Adapter\AbstractRequest::setParameters
     * @covers  Molajo\Route\Adapter\AbstractRequest::removePathSlash
     * @covers  Molajo\Route\Adapter\AbstractRequest::getParameters
     * @covers  Molajo\Route\Adapter\AbstractRequest::getParameterPairs
     * @covers  Molajo\Route\Adapter\AbstractRequest::getPath
     * @covers  Molajo\Route\Adapter\AbstractRequest::getNode
     * @covers  Molajo\Route\Adapter\AbstractRequest::setIndexAtMax
     * @covers  Molajo\Route\Adapter\AbstractRequest::decrementIndex
     * @covers  Molajo\Route\Adapter\AbstractRequest::parseParameterPair
     * @covers  Molajo\Route\Adapter\AbstractRequest::setPageType
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHome
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHomeEmptyPath
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHomeSlash
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHomeIndex
     * @covers  Molajo\Route\Adapter\AbstractAdapter::initialiseRoute
     * @covers  Molajo\Route\Adapter\AbstractAdapter::verifySecureProtocol
     *
     * @return  $this
     * @since   1.0
     */
    public function testVerifySecureProtocolNotSSL()
    {
        $adapter     = $this->getAdapter($url_force_ssl = 0, $is_secure = 1);
        $instance = $this->getRouteDriver($adapter);

        $route = $instance->verifySecureProtocol();

        $this->assertEquals($route->route_found, null);
        $this->assertEquals($route->error_code, 0);
        $this->assertEquals($route->redirect_to_id, null);
        $this->assertEquals($route->home, 0);
        $this->assertEquals($route->catalog_id, 0);
        $this->assertEquals($route->action, '');
        $this->assertEquals($route->method, '');
        $this->assertEquals($route->base_url, '');
        $this->assertEquals($route->path, '');
        $this->assertEquals($route->post_variable_array, array());
        $this->assertEquals($route->filters, array());
        $this->assertEquals($route->model_name, '');
        $this->assertEquals($route->model_type, '');
        $this->assertEquals($route->model_registry_name, '');
        $this->assertEquals($route->page_type, '');

        return $this;
    }

    /**
     * verifySecureProtocol -- SSL
     *
     * @covers  Molajo\Route\Driver::__construct
     * @covers  Molajo\Route\Driver::verifySecureProtocol
     * @covers  Molajo\Route\Driver::verifyHome
     * @covers  Molajo\Route\Driver::setRequest
     * @covers  Molajo\Route\Driver::setRoute
     * @covers  Molajo\Route\Adapter\Database::__construct
     * @covers  Molajo\Route\Adapter\Database::setRoute
     * @covers  Molajo\Route\Adapter\AbstractRequest::setRequest
     * @covers  Molajo\Route\Adapter\AbstractRequest::setAction
     * @covers  Molajo\Route\Adapter\AbstractRequest::setBaseUrl
     * @covers  Molajo\Route\Adapter\AbstractRequest::setRequestVariables
     * @covers  Molajo\Route\Adapter\AbstractRequest::setParameters
     * @covers  Molajo\Route\Adapter\AbstractRequest::removePathSlash
     * @covers  Molajo\Route\Adapter\AbstractRequest::getParameters
     * @covers  Molajo\Route\Adapter\AbstractRequest::getParameterPairs
     * @covers  Molajo\Route\Adapter\AbstractRequest::getPath
     * @covers  Molajo\Route\Adapter\AbstractRequest::getNode
     * @covers  Molajo\Route\Adapter\AbstractRequest::setIndexAtMax
     * @covers  Molajo\Route\Adapter\AbstractRequest::decrementIndex
     * @covers  Molajo\Route\Adapter\AbstractRequest::parseParameterPair
     * @covers  Molajo\Route\Adapter\AbstractRequest::setPageType
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHome
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHomeEmptyPath
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHomeSlash
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHomeIndex
     * @covers  Molajo\Route\Adapter\AbstractAdapter::initialiseRoute
     * @covers  Molajo\Route\Adapter\AbstractAdapter::verifySecureProtocol
     *
     * @return  $this
     * @since   1.0
     */
    public function testVerifySecureProtocolSSL()
    {
        $adapter     = $this->getAdapter($url_force_ssl = 0, $is_secure = 1);
        $instance = $this->getRouteDriver($adapter);

        $route = $instance->verifySecureProtocol();

        $this->assertEquals($route->route_found, null);
        $this->assertEquals($route->error_code, 0);
        $this->assertEquals($route->redirect_to_id, null);
        $this->assertEquals($route->home, 0);
        $this->assertEquals($route->catalog_id, 0);
        $this->assertEquals($route->action, '');
        $this->assertEquals($route->method, '');
        $this->assertEquals($route->base_url, '');
        $this->assertEquals($route->path, '');
        $this->assertEquals($route->post_variable_array, array());
        $this->assertEquals($route->filters, array());
        $this->assertEquals($route->model_name, '');
        $this->assertEquals($route->model_type, '');
        $this->assertEquals($route->model_registry_name, '');
        $this->assertEquals($route->page_type, '');

        return $this;
    }

    /**
     * verifySecureProtocol -- Error
     *
     * @covers  Molajo\Route\Driver::__construct
     * @covers  Molajo\Route\Driver::verifySecureProtocol
     * @covers  Molajo\Route\Driver::verifyHome
     * @covers  Molajo\Route\Driver::setRequest
     * @covers  Molajo\Route\Driver::setRoute
     * @covers  Molajo\Route\Adapter\Database::__construct
     * @covers  Molajo\Route\Adapter\Database::setRoute
     * @covers  Molajo\Route\Adapter\AbstractRequest::setRequest
     * @covers  Molajo\Route\Adapter\AbstractRequest::setAction
     * @covers  Molajo\Route\Adapter\AbstractRequest::setBaseUrl
     * @covers  Molajo\Route\Adapter\AbstractRequest::setRequestVariables
     * @covers  Molajo\Route\Adapter\AbstractRequest::setParameters
     * @covers  Molajo\Route\Adapter\AbstractRequest::removePathSlash
     * @covers  Molajo\Route\Adapter\AbstractRequest::getParameters
     * @covers  Molajo\Route\Adapter\AbstractRequest::getParameterPairs
     * @covers  Molajo\Route\Adapter\AbstractRequest::getPath
     * @covers  Molajo\Route\Adapter\AbstractRequest::getNode
     * @covers  Molajo\Route\Adapter\AbstractRequest::setIndexAtMax
     * @covers  Molajo\Route\Adapter\AbstractRequest::decrementIndex
     * @covers  Molajo\Route\Adapter\AbstractRequest::parseParameterPair
     * @covers  Molajo\Route\Adapter\AbstractRequest::setPageType
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHome
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHomeEmptyPath
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHomeSlash
     * @covers  Molajo\Route\Adapter\AbstractVerifyHome::verifyHomeIndex
     * @covers  Molajo\Route\Adapter\AbstractAdapter::initialiseRoute
     * @covers  Molajo\Route\Adapter\AbstractAdapter::verifySecureProtocol
     *
     * @return  $this
     * @since   1.0
     */
    public function testVerifySecureProtocolError()
    {
        $adapter     = $this->getAdapter($url_force_ssl = 1, $is_secure = 0);
        $instance = $this->getRouteDriver($adapter);

        $route = $instance->verifySecureProtocol();

        $this->assertEquals($route->route_found, null);
        $this->assertEquals($route->error_code, 301);
        $this->assertEquals($route->redirect_to_id, 1072);
        $this->assertEquals($route->home, 0);
        $this->assertEquals($route->catalog_id, 0);
        $this->assertEquals($route->action, '');
        $this->assertEquals($route->method, '');
        $this->assertEquals($route->base_url, '');
        $this->assertEquals($route->path, '');
        $this->assertEquals($route->post_variable_array, array());
        $this->assertEquals($route->filters, array());
        $this->assertEquals($route->model_name, '');
        $this->assertEquals($route->model_type, '');
        $this->assertEquals($route->model_registry_name, '');
        $this->assertEquals($route->page_type, '');

        return $this;
    }
}

