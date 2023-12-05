<?php

namespace Tamm\Core\Skelton;

require_once(__DIR__.'/bootstrap.php');

use Tamm\Core\Skelton\Bootstrap;
use Tamm\Core\Skelton\Container;
use Tamm\Core\Skelton\Orienter;
use Tamm\Core\Skelton\HttpRequest;
use Tamm\Core\Skelton\HttpResponse;

/**
 * Class Application
 *
 * @author  Abdullah Sowailem <abdullah.sowailem@gmail.com>
 * @package Tamm\Core\Skelton
 */
class Application {

    //
    public const VERSION = "1.0.0";
    // "/var/www/html/tammphp/"
    public string $rootPath;
    // "/tammphp/" or just "/"
    public string $basePath;
    //
    private static Bootstrap $bootstrap;
    //
    private static Container $container;
    //
    private Orienter $orinter;
    //
    private $configuration;
    //
    private $middlewares = [];

    // The only way we can get an object from Application class
    // by the method build().
    private function __construct($configuration = array())
    {

        $this->configuration       = $configuration;

        $dir = dirname(__DIR__);
        $this->basePath = $configuration['base_path'];
        $path = explode($this->basePath, $dir);
        $this->rootPath = $path[0].$this->basePath;
        
        // $this->bootstrap        = new Bootstrap();
        // $this->container        = new Container();
        // //
        // $this->container->set(new Router());
        // // $this->middlewares      = $configuration['middlewares'];
    }

    public static function build($configuration = array()){
        self::$bootstrap = new Bootstrap(new self($configuration));
        self::$container = self::$bootstrap->getContainer();

        //
        self::$container->set(new Orienter());

        //
        return self::$bootstrap->getApplication();
    }

    //
    public function getContainer(){
        return $this->container;
    }

    //
    public function getBasePath(){
        return $this->basePath;
    }

    //
    public function getRootPath(){
        return $this->rootPath;
    }

    public function addMiddleware(IMiddleware $middleware) {
        $this->middlewares[] = $middleware;
    }

    public function handleRequest(HttpRequest $request) {
        // Create a closure representing the final application logic
        $applicationLogic = function (HttpRequest $request) {
            // Process the request and generate a response
            $response = new HttpResponse(200,array(),"Hellow");
            return $response;
        };

        // Build the middleware stack in reverse order
        $middlewares = array_reverse($this->middlewares);

        // Wrap the application logic with each middleware in the stack
        foreach ($middlewares as $middleware) {
            $applicationLogic = function (HttpRequest $request) use ($middleware, $applicationLogic) {
                return $middleware->process($request, $applicationLogic);
            };
        }

        // Start the request processing with the outermost middleware
        $response = $applicationLogic($request);

        // Return the final response
        return $response;
    }

    public function run(){
        self::$bootstrap->loadControllersFromModules();
        self::$bootstrap->handleHttpRequest(self::$container);

        // // Create a closure representing the final application logic
        // $applicationLogic = function (HttpRequest $httpRequest) {
        //     // Process the HttpRequest and generate a response
        //     // @TODO
        //     $statusCode = 200; 
        //     $headers = array(); 
        //     $body = "Test the request";
        //     $response = new HttpResponse($statusCode, $headers, $body);
        //     $response->setHeader('X-Framework','TammPHP '.self::VERSION);
        //     return $response;
        // };
        // // Start the HttpRequest processing with the outermost middleware
        // $response = $applicationLogic(self::$container->get(HttpRequest::class));
        

        // $response = self::$container->get(HttpRequest::class);
        $statusCode = 200; 
        $headers = array(); 
        $body = "Test the request";
        $response = new HttpResponse($statusCode, $headers, $body);

        // Return the final response
        return $response;

    }
}