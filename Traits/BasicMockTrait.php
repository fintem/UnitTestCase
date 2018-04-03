<?php

namespace Fintem\UnitTestCase\Traits;

use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Trait BasicMockTrait.
 *
 * @method \PHPUnit_Framework_MockObject_MockBuilder getMockBuilder(string $className)
 */
trait BasicMockTrait
{
    /**
     * @param string $class
     * @param array  $arguments
     * @param array  $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     *
     * @throws Exception
     */
    public function getBasicMock(string $class, array $arguments = null, array $methods = null) : \PHPUnit_Framework_MockObject_MockObject
    {
        $mockBuilder = $this->getMockBuilder($class);
        if (null === $arguments) {
            $mockBuilder->disableOriginalConstructor();
        } else {
            $constructorArgs = $this->getConstructorArguments($class, $arguments);
            $mockBuilder->setConstructorArgs($constructorArgs);
        }

        return $mockBuilder->setMethods($methods)->getMock();
    }

    /**
     * @param string $class
     * @param array  $arguments
     *
     * @return array
     *
     * @throws Exception
     */
    private function getConstructorArguments(string $class, array $arguments) : array
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if (null === $constructor) {
            return [];
        }
        $parameters = $constructor->getParameters();
        $constructorArgs = [];
        foreach ($parameters as $parameter) {
            $parameterName = $parameter->name;
            if (array_key_exists($parameter->name, $arguments)) {
                $constructorArgs[] = $arguments[$parameterName];
                unset($arguments[$parameterName]);
            } else {
                $className = $parameter->getClass()->name;
                if (null === $className) {
                    try {
                        $constructorArgs[] = $parameter->getDefaultValue();
                        continue;
                    } catch (ReflectionException $exception) {
                        throw new \Exception(
                            sprintf('Can\'t build mock for parameter "%s" for "%s".', $parameterName, $class),
                            0,
                            $exception
                        );
                    }
                }

                $constructorArgs[] = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
            }
        }
        if (!empty($arguments)) {
            throw new \Exception(
                sprintf('Non existing argument%s "%s" passed to "%s" mock constructor.',
                    count($arguments) > 1 ? 's' : '',
                    implode(', ', array_keys($arguments)),
                    $class
                )
            );
        }

        return $constructorArgs;
    }
}
