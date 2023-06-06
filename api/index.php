<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Store\Toys\Robots;

echo "HI";
$loader = new Loader();

$loader->registerNamespaces(
    [
        'Store\Toys' => __DIR__ . '/models/',
    ]
);

$loader->register();

$di = new FactoryDefault();

// Set up the database service
$di->set(
    'db',
    function () {
        return new PdoMysql(
            [
                'host'     => 'mysql-server',
                'username' => 'root',
                'password' => 'secret',
                'dbname'   => 'robotics',
            ]
        );
    }
);

// Create and bind the DI to the application
$app = new Micro($di);


// Retrieves all robots
$app->get(
    '/api/robots',
    function () {
        $result = Robots::find();
        echo json_encode($result);
    }
);

// Searches for robots with $name in their name
$app->get(
    '/api/robots/search/{name}',
    function ($name) {
        $robot = new Robots();
        $result = $robot->findFirst(
            [
                'conditions' => "name = '$name'"
            ]
        );
        echo json_encode($result);
    }
);

// Retrieves robots based on primary key
$app->get(
    '/api/robots/{id:[0-9]+}',
    function ($id) {
        $robot = new Robots();
        $result = $robot->findFirst(
            [
                'conditions' => "id = '$id'"
            ]
        );
        echo json_encode($result);
    }
);

// Adds a new robot
$app->post(
    '/api/robots',
    function () use ($app) {
        $request = $app->request->getJsonRawBody();
        $data = array("name" => $request->name, "type" => $request->type, "year" => $request->year);
        $robot = new Robots();
        $robot->assign(
            $data,
            [
                'name',
                'type',
                'year'
            ]
        );
        $success = $robot->save();
        if ($success) {
            echo "Data Successfully Added";
        } else {
            echo "Error";
        }
    }
);

// Updates robots based on primary key
$app->put(
    '/api/robots/{id:[0-9]+}',
    function ($id) use ($app) {
        // Operation to update a robot with id $id
        $request = $app->request->getJsonRawBody();
        $data = Robots::findFirst('id =' . $id);
        $data->name = $request->name;
        $data->type = $request->type;
        $data->year = $request->year;
        $success = $data->update();
        if ($success) {
            echo "Data Successfully Updated";
        } else {
            echo "Error";
        }
    }
);

// Deletes robots based on primary key
$app->delete(
    '/api/robots/{id:[0-9]+}',
    function ($id) {
        // Operation to delete the robot with id $id
        $data = Robots::findFirst('id =' . $id);
        $result  = $data->delete();
        if ($result) {
            echo "Data Successfully Deleted";
        } else {
            echo "Error";
        }
    }
);
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo 'This is crazy, but this page was not found!';
});
$app->handle(
    $_SERVER["REQUEST_URI"]
);
