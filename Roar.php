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
    public function input($dependencies)
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
    public function get($driver = null)
    {
        try {
            $namespace = $this->input->driver[$driver]->namespace;
            if ((($driver === null) OR empty($driver)) && !class_exists($namespace)) {
                throw new \RuntimeException("Driver or namespace is invalid!");                
            }
            
            $driver = $this->input->driver[$driver];
            return (object) $this->container = $this->_instantiate($driver);

        } catch(\RuntimeException $ex) {
            echo $ex->getMessage();
        }
    }
    
    /**
     * Instantiate the object with or without arguments dependencies
     * 
     * @param object $driver Object containing
     * 
     * @return object
     */
    private function _instantiate($driver)
    {   
        if (isset($driver->method)) {
            foreach ($driver->method as $key => $method) {
                $args = $this->_findArguments($driver->method[$key]);                
                if ($key == '__construct') {
                    $class = new \ReflectionClass($driver->namespace);                
                    $this->container = $class->newInstanceArgs($args);
                } else {
                    $reflectionMethod = new \ReflectionMethod(
                        $this->container, $key
                    );
                    $this->container->{$key} = $reflectionMethod->invokeArgs(
                        $this->container, $args
                    );                    
                }
            }            
        } else {
            $this->container = new $driver->namespace();
        }      

        return (object) $this->container;
    }
    
    /**
     * Get all Arguments specified
     * If argument is a driver or an array, instantiate it in arg variaable
     * 
     * @param array $methodArgs All arguments of the methods with name
     * 
     * @return array
     */
    private function _findArguments($methodArgs)
    {
        $args = array();
        
        foreach ($methodArgs as $argument) {
            $args[] = $this->_argumentStrategy($argument);
        }
        
        return (array) $args;
    }
    
    /**
     * Strategy for getting arguments
     * 
     * @param string $argument Argument of _findArguments array
     * 
     * @return mixed
     */
    private function _argumentStrategy($argument)
    {
        if (substr($argument, 0, 1) == '%') {
            $newArgument = str_replace('%', '', $argument);
            $value = (object) $this->get($newArgument);
        } elseif (substr($argument, 0, 6) == 'array|') {
            $newArgument = str_replace('array|', '', $argument);
            $value = (array) array($newArgument);                
        } else {
            $value = (string) $argument;         
        }
        
        return $value;
    }
}
