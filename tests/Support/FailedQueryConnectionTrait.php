<?php

namespace Tests\Support;

use CodeIgniter\BaseModel;
use CodeIgniter\Database\BaseConnection;

trait FailedQueryConnectionTrait
{
    protected function failingConnection(): BaseConnection
    {
        $driverClass = get_class(\Config\Database::connect($this->DBGroup));

        $db = $this->getMockBuilder($driverClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['query', 'error'])
            ->getMock();
        $db->method('query')->willReturn(false);
        $db->method('error')->willReturn(['code' => 1, 'message' => 'simulated query failure']);

        return $db;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    protected function modelWithFailingQuery(string $class)
    {
        $model = new $class();

        $prop = new \ReflectionProperty(BaseModel::class, 'db');
        $prop->setAccessible(true);
        $prop->setValue($model, $this->failingConnection());

        return $model;
    }
}
