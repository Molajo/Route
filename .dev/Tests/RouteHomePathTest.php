<?php
/**
 * Route Home Path
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route;

use stdClass;
use Molajo\Route\Controller\Database;
use Molajo\Query\MockReadController;

use Molajo\Route\Controller;

/**
 * Route Home Path
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class RouteHomePath extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Object
     */
    protected $route;

    /**
     * Get the Route Adapter Handler
     *
     * @covers  Molajo\Route\Controller::__construct
     * @covers  Molajo\Route\Controller::verifySecureProtocol
     * @covers  Molajo\Route\Controller::verifyHome
     * @covers  Molajo\Route\Controller::setRequest
     * @covers  Molajo\Route\Controller::setRoute
     * @covers  Molajo\Route\Controller\Database::__construct
     * @covers  Molajo\Route\Controller\Database::setRoute
     * @covers  Molajo\Route\Controller\AbstractRequest::setRequest
     * @covers  Molajo\Route\Controller\AbstractRequest::setAction
     * @covers  Molajo\Route\Controller\AbstractRequest::setBaseUrl
     * @covers  Molajo\Route\Controller\AbstractRequest::setRequestVariables
     * @covers  Molajo\Route\Controller\AbstractRequest::setParameters
     * @covers  Molajo\Route\Controller\AbstractRequest::removePathSlash
     * @covers  Molajo\Route\Controller\AbstractRequest::getParameters
     * @covers  Molajo\Route\Controller\AbstractRequest::getParameterPairs
     * @covers  Molajo\Route\Controller\AbstractRequest::getPath
     * @covers  Molajo\Route\Controller\AbstractRequest::getNode
     * @covers  Molajo\Route\Controller\AbstractRequest::setIndexAtMax
     * @covers  Molajo\Route\Controller\AbstractRequest::decrementIndex
     * @covers  Molajo\Route\Controller\AbstractRequest::parseParameterPair
     * @covers  Molajo\Route\Controller\AbstractRequest::setPageType
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHome
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeEmptyPath
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeSlash
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeIndex
     * @covers  Molajo\Route\Controller\AbstractAdapter::initialiseRoute
     * @covers  Molajo\Route\Controller\AbstractAdapter::verifySecureProtocol
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
     *
     * @covers  Molajo\Route\Controller::__construct
     * @covers  Molajo\Route\Controller::verifySecureProtocol
     * @covers  Molajo\Route\Controller::verifyHome
     * @covers  Molajo\Route\Controller::setRequest
     * @covers  Molajo\Route\Controller::setRoute
     * @covers  Molajo\Route\Controller\Database::__construct
     * @covers  Molajo\Route\Controller\Database::setRoute
     * @covers  Molajo\Route\Controller\AbstractRequest::setRequest
     * @covers  Molajo\Route\Controller\AbstractRequest::setAction
     * @covers  Molajo\Route\Controller\AbstractRequest::setBaseUrl
     * @covers  Molajo\Route\Controller\AbstractRequest::setRequestVariables
     * @covers  Molajo\Route\Controller\AbstractRequest::setParameters
     * @covers  Molajo\Route\Controller\AbstractRequest::removePathSlash
     * @covers  Molajo\Route\Controller\AbstractRequest::getParameters
     * @covers  Molajo\Route\Controller\AbstractRequest::getParameterPairs
     * @covers  Molajo\Route\Controller\AbstractRequest::getPath
     * @covers  Molajo\Route\Controller\AbstractRequest::getNode
     * @covers  Molajo\Route\Controller\AbstractRequest::setIndexAtMax
     * @covers  Molajo\Route\Controller\AbstractRequest::decrementIndex
     * @covers  Molajo\Route\Controller\AbstractRequest::parseParameterPair
     * @covers  Molajo\Route\Controller\AbstractRequest::setPageType
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHome
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeEmptyPath
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeSlash
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeIndex
     * @covers  Molajo\Route\Controller\AbstractAdapter::initialiseRoute
     * @covers  Molajo\Route\Controller\AbstractAdapter::verifySecureProtocol
     */
    protected function getRouteDriver($adapter)
    {
        return new Driver($adapter);
    }

    /**
     * verifyHome -- verifyHomeEmptyPath
     *
     * @covers  Molajo\Route\Controller::__construct
     * @covers  Molajo\Route\Controller::verifySecureProtocol
     * @covers  Molajo\Route\Controller::verifyHome
     * @covers  Molajo\Route\Controller::setRequest
     * @covers  Molajo\Route\Controller::setRoute
     * @covers  Molajo\Route\Controller\Database::__construct
     * @covers  Molajo\Route\Controller\Database::setRoute
     * @covers  Molajo\Route\Controller\AbstractRequest::setRequest
     * @covers  Molajo\Route\Controller\AbstractRequest::setAction
     * @covers  Molajo\Route\Controller\AbstractRequest::setBaseUrl
     * @covers  Molajo\Route\Controller\AbstractRequest::setRequestVariables
     * @covers  Molajo\Route\Controller\AbstractRequest::setParameters
     * @covers  Molajo\Route\Controller\AbstractRequest::removePathSlash
     * @covers  Molajo\Route\Controller\AbstractRequest::getParameters
     * @covers  Molajo\Route\Controller\AbstractRequest::getParameterPairs
     * @covers  Molajo\Route\Controller\AbstractRequest::getPath
     * @covers  Molajo\Route\Controller\AbstractRequest::getNode
     * @covers  Molajo\Route\Controller\AbstractRequest::setIndexAtMax
     * @covers  Molajo\Route\Controller\AbstractRequest::decrementIndex
     * @covers  Molajo\Route\Controller\AbstractRequest::parseParameterPair
     * @covers  Molajo\Route\Controller\AbstractRequest::setPageType
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHome
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeEmptyPath
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeSlash
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeIndex
     * @covers  Molajo\Route\Controller\AbstractAdapter::initialiseRoute
     * @covers  Molajo\Route\Controller\AbstractAdapter::verifySecureProtocol
     *
     * @return  $this
     * @since   1.0.0
     */
    public function testVerifyHomeEmptyPath()
    {
        $adapter  = $this->getAdapter(
            $url_force_ssl = 0,
            $is_secure = 1,
            $application_path = ''
        );
        $instance = $this->getRouteDriver($adapter);

        $route = $instance->verifyHome();

        $this->assertEquals($route->route_found, null);
        $this->assertEquals($route->error_code, 0);
        $this->assertEquals($route->redirect_to_url, null);
        $this->assertEquals($route->home, 1);
        $this->assertEquals($route->catalog_id, 1072);
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
     * verifyHome -- verifyHomeSlash -- Redirect
     *
     * @covers  Molajo\Route\Controller::__construct
     * @covers  Molajo\Route\Controller::verifySecureProtocol
     * @covers  Molajo\Route\Controller::verifyHome
     * @covers  Molajo\Route\Controller::setRequest
     * @covers  Molajo\Route\Controller::setRoute
     * @covers  Molajo\Route\Controller\Database::__construct
     * @covers  Molajo\Route\Controller\Database::setRoute
     * @covers  Molajo\Route\Controller\AbstractRequest::setRequest
     * @covers  Molajo\Route\Controller\AbstractRequest::setAction
     * @covers  Molajo\Route\Controller\AbstractRequest::setBaseUrl
     * @covers  Molajo\Route\Controller\AbstractRequest::setRequestVariables
     * @covers  Molajo\Route\Controller\AbstractRequest::setParameters
     * @covers  Molajo\Route\Controller\AbstractRequest::removePathSlash
     * @covers  Molajo\Route\Controller\AbstractRequest::getParameters
     * @covers  Molajo\Route\Controller\AbstractRequest::getParameterPairs
     * @covers  Molajo\Route\Controller\AbstractRequest::getPath
     * @covers  Molajo\Route\Controller\AbstractRequest::getNode
     * @covers  Molajo\Route\Controller\AbstractRequest::setIndexAtMax
     * @covers  Molajo\Route\Controller\AbstractRequest::decrementIndex
     * @covers  Molajo\Route\Controller\AbstractRequest::parseParameterPair
     * @covers  Molajo\Route\Controller\AbstractRequest::setPageType
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHome
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeEmptyPath
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeSlash
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeIndex
     * @covers  Molajo\Route\Controller\AbstractAdapter::initialiseRoute
     * @covers  Molajo\Route\Controller\AbstractAdapter::verifySecureProtocol
     *
     * @return  $this
     * @since   1.0.0
     */
    public function testVerifyHomeSlash()
    {
        $adapter  = $this->getAdapter(
            $url_force_ssl = 0,
            $is_secure = 1,
            $application_path = '/'
        );
        $instance = $this->getRouteDriver($adapter);

        $route = $instance->verifyHome();

        $this->assertEquals($route->route_found, null);
        $this->assertEquals($route->error_code, 301);
        $this->assertEquals($route->redirect_to_url, 1072);
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
     * verifyHome -- verifyHomeSlash -- Redirect
     *
     * @covers  Molajo\Route\Controller::__construct
     * @covers  Molajo\Route\Controller::verifySecureProtocol
     * @covers  Molajo\Route\Controller::verifyHome
     * @covers  Molajo\Route\Controller::setRequest
     * @covers  Molajo\Route\Controller::setRoute
     * @covers  Molajo\Route\Controller\Database::__construct
     * @covers  Molajo\Route\Controller\Database::setRoute
     * @covers  Molajo\Route\Controller\AbstractRequest::setRequest
     * @covers  Molajo\Route\Controller\AbstractRequest::setAction
     * @covers  Molajo\Route\Controller\AbstractRequest::setBaseUrl
     * @covers  Molajo\Route\Controller\AbstractRequest::setRequestVariables
     * @covers  Molajo\Route\Controller\AbstractRequest::setParameters
     * @covers  Molajo\Route\Controller\AbstractRequest::removePathSlash
     * @covers  Molajo\Route\Controller\AbstractRequest::getParameters
     * @covers  Molajo\Route\Controller\AbstractRequest::getParameterPairs
     * @covers  Molajo\Route\Controller\AbstractRequest::getPath
     * @covers  Molajo\Route\Controller\AbstractRequest::getNode
     * @covers  Molajo\Route\Controller\AbstractRequest::setIndexAtMax
     * @covers  Molajo\Route\Controller\AbstractRequest::decrementIndex
     * @covers  Molajo\Route\Controller\AbstractRequest::parseParameterPair
     * @covers  Molajo\Route\Controller\AbstractRequest::setPageType
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHome
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeEmptyPath
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeSlash
     * @covers  Molajo\Route\Controller\AbstractVerifyHome::verifyHomeIndex
     * @covers  Molajo\Route\Controller\AbstractAdapter::initialiseRoute
     * @covers  Molajo\Route\Controller\AbstractAdapter::verifySecureProtocol
     *
     * @return  $this
     * @since   1.0.0
     */
    public function testVerifyHomeIndex()
    {
        $adapter  = $this->getAdapter(
            $url_force_ssl = 0,
            $is_secure = 1,
            $application_path = 'index.php'
        );
        $instance = $this->getRouteDriver($adapter);

        $route = $instance->verifyHome();

        $this->assertEquals($route->route_found, null);
        $this->assertEquals($route->error_code, 301);
        $this->assertEquals($route->redirect_to_url, 1072);
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
