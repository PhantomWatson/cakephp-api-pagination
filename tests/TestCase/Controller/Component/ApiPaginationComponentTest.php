<?php
namespace BryanCrowe\ApiPagination\Test;

use BryanCrowe\ApiPagination\Controller\Component\ApiPaginationComponent;
use BryanCrowe\ApiPagination\TestApp\Controller\ArticlesController;
use Cake\Event\Event;
use Cake\Http\ServerRequest as Request;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * ApiPaginationComponentTest class
 *
 * @property ArticlesController $controller
 */
class ApiPaginationComponentTest extends TestCase
{
    public $fixtures = ['plugin.BryanCrowe/ApiPagination.Articles'];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        $this->request = new Request('/articles');
        $this->response = $this->createMock('Cake\Http\Response');
        $this->controller = new ArticlesController($this->request, $this->response);
        $this->Articles = TableRegistry::get('BryanCrowe/ApiPagination.Articles', ['table' => 'bryancrowe_articles']);
        parent::setUp();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test that a non API or paginated request returns null.
     *
     * @return void
     */
    public function testNonApiPaginatedRequest()
    {
        $apiPaginationComponent = new ApiPaginationComponent($this->controller->components());
        $event = new Event('Controller.beforeRender', $this->controller);

        $this->assertNull($apiPaginationComponent->beforeRender($event));
    }

    /**
     * Test the expected pagination information for the component's default
     * config.
     *
     * @return void
     */
    public function testDefaultPaginationSettings()
    {
        $this->controller->request = $this->controller->request->withEnv('HTTP_ACCEPT', 'application/json');
        $this->controller->set('data', $this->controller->paginate($this->Articles));
        $apiPaginationComponent = new ApiPaginationComponent($this->controller->components());
        $event = new Event('Controller.beforeRender', $this->controller);
        $apiPaginationComponent->beforeRender($event);

        $result = $apiPaginationComponent->_registry->getController()->viewVars['pagination'];
        $expected = [
            'finder' => 'all',
            'page' => 1,
            'current' => 20,
            'count' => 23,
            'perPage' => 20,
            'prevPage' => false,
            'nextPage' => true,
            'pageCount' => 2,
            'sort' => null,
            'direction' => false,
            'limit' => null,
            'sortDefault' => false,
            'directionDefault' => false,
            'scope' => null,
            'completeSort' => []
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test that visibility-only correctly sets the visible keys.
     *
     * @return void
     */
    public function testVisibilitySettings()
    {
        $this->controller->request = $this->controller->request->withEnv('HTTP_ACCEPT', 'application/json');
        $this->controller->set('data', $this->controller->paginate($this->Articles));
        $apiPaginationComponent = new ApiPaginationComponent($this->controller->components(), [
            'visible' => [
                'page',
                'current',
                'count',
                'prevPage',
                'nextPage',
                'pageCount'
            ]
        ]);
        $event = new Event('Controller.beforeRender', $this->controller);
        $apiPaginationComponent->beforeRender($event);

        $result = $apiPaginationComponent->_registry->getController()->viewVars['pagination'];
        $expected = [
            'page' => 1,
            'current' => 20,
            'count' => 23,
            'prevPage' => false,
            'nextPage' => true,
            'pageCount' => 2
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test that alias-only correctly sets aliases the keys.
     *
     * @return void
     */
    public function testAliasSettings()
    {
        $this->controller->request = $this->controller->request->withEnv('HTTP_ACCEPT', 'application/json');
        $this->controller->set('data', $this->controller->paginate($this->Articles));
        $apiPaginationComponent = new ApiPaginationComponent($this->controller->components(), [
            'aliases' => [
                'page' => 'curPage',
                'current' => 'currentCount',
                'count' => 'totalCount',
            ]
        ]);
        $event = new Event('Controller.beforeRender', $this->controller);
        $apiPaginationComponent->beforeRender($event);

        $result = $apiPaginationComponent->_registry->getController()->viewVars['pagination'];
        $expected = [
            'finder' => 'all',
            'perPage' => 20,
            'prevPage' => false,
            'nextPage' => true,
            'pageCount' => 2,
            'sort' => null,
            'direction' => false,
            'limit' => null,
            'sortDefault' => false,
            'directionDefault' => false,
            'scope' => null,
            'completeSort' => [],
            'curPage' => 1,
            'currentCount' => 20,
            'totalCount' => 23,
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test that key-only correctly sets the pagination key.
     *
     * @return void
     */
    public function testKeySetting()
    {
        $this->controller->request = $this->controller->request->withEnv('HTTP_ACCEPT', 'application/json');
        $this->controller->set('data', $this->controller->paginate($this->Articles));
        $apiPaginationComponent = new ApiPaginationComponent($this->controller->components(), [
            'key' => 'paging'
        ]);
        $event = new Event('Controller.beforeRender', $this->controller);
        $apiPaginationComponent->beforeRender($event);

        $result = $apiPaginationComponent->_registry->getController()->viewVars['paging'];
        $expected = [
            'finder' => 'all',
            'page' => 1,
            'current' => 20,
            'count' => 23,
            'perPage' => 20,
            'prevPage' => false,
            'nextPage' => true,
            'pageCount' => 2,
            'sort' => null,
            'direction' => false,
            'limit' => null,
            'sortDefault' => false,
            'directionDefault' => false,
            'scope' => null,
            'completeSort' => []
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test that all settings being used together work correctly.
     *
     * @return void
     */
    public function testAllSettings()
    {
        $this->controller->request = $this->controller->request->withEnv('HTTP_ACCEPT', 'application/json');
        $this->controller->set('data', $this->controller->paginate($this->Articles));
        $apiPaginationComponent = new ApiPaginationComponent($this->controller->components(), [
            'key' => 'fun',
            'aliases' => [
                'page' => 'currentPage',
                'count' => 'totalCount',
                'limit' => 'unusedAlias'
            ],
            'visible' => [
                'currentPage',
                'totalCount',
                'limit',
                'prevPage',
                'nextPage'
            ]
        ]);
        $event = new Event('Controller.beforeRender', $this->controller);
        $apiPaginationComponent->beforeRender($event);

        $result = $apiPaginationComponent->_registry->getController()->viewVars['fun'];
        $expected = [
            'prevPage' => false,
            'nextPage' => true,
            'currentPage' => 1,
            'totalCount' => 23,
        ];

        $this->assertSame($expected, $result);
    }
}
