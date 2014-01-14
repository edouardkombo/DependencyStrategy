<?php

/**
 * Main docblock
 *
 * PHP version 5
 *
 * @category  Roar
 * @package   DependencyStrategy
 * @author    Edouard Kombo <edouard.kombo@gmail.com>
 * @copyright 2013-2014 Edouard Kombo
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   GIT: 1.0.0
 * @link      http://www.breezeframework.com/thetrollinception.php
 * @since     1.0.0
 */
namespace TTI\DependencyStrategy;

use TTI\AbstractFactory\HandleAbstraction;

/**
 * Roar responsibility is to handle dependency injection and container.
 *
 * @category Roar
 * @package  DependencyStrategy
 * @author   Edouard Kombo <edouard.kombo@gmail.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://www.breezeframework.com/thetrollinception.php
 */
class Roar extends HandleAbstraction
{
    /**
     *
     * @var stdClass $input
     */
    protected $input;    

    /**
     *
     * @var stdCLass $container
     */
    protected $container;

    /**
     * Constructor
     */
    public function __construct()
    {
    }
    
    /**
     * Cloner
     * 
     * @return void
     */
    public function __clone()
    {
    }      
    
    /**
     * Inject dependencies
     * 
     * @param stdClass $dependencies Dependencies as objects
     * 
     * @return \TTI\DependencyStrategy\Roar
     */
    protected function input($dependencies)
    {
        $this->input = $dependencies;
        
        return (object) $this;
    }  
    
    /**
     * Get specified object from dependency
     *
     * @param string $driver Key for driver class
     * 
     * @throws \RuntimeException
     * @return object
     */
    public function get($driver)
    {
        try {
            $namespace = $this->input->driver[$driver]->namespace;
            if ((($driver === null) OR empty($driver)) && !class_exists($namespace)) {
                throw new \RuntimeException("Driver or namespace is invalid!");                
            }
            
            $driver = $this->input->driver[$driver];
            return (object) $this->container = $this->_instantiate($driver, $namespace);

        } catch(\RuntimeException $ex) {
            $ex->getMessage();   
        }
    }
    
    /**
     * Instantiate the object with or without arguments dependencies
     * 
     * @param object $driver    Object containing
     * @param string $namespace Namespace of the class
     * 
     * @return object
     */
    private function _instantiate($driver, $namespace)
    {
        if (!isset($driver->method['__construct'])) {
            $this->container = new $namespace();
        } else {
            $args = $this->_findArguments($driver->method['__construct']);
            $class = new \ReflectionClass($namespace);
            $this->container = $class->newInstanceArgs($args);                
        }
        
        return (object) $this->container;
    }
    
    /**
     * Only get single arguments in an array
     * 
     * @param array $methodArgs All arguments of the methods with name
     * 
     * @return array
     */
    private function _findArguments($methodArgs)
    {
        $args = array();
        
        foreach ($methodArgs as $argument) {
            $args[] = (string) $argument;         
        }
        
        return (array) $args;
    }
}
