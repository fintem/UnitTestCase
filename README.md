UnitTestCase
============

Simplifies usage of php mocks, allows to mock only the necessary constructor arguments.

```php
$model = $this->getBasicMock(ApplicationModel::class, ['dispatcher' => $dispatcher]);
```