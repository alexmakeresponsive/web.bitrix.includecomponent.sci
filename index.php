<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/loader.php';

\spl_autoload_register([Loader::class, 'autoLoad']);

Loader::switchAutoLoad(true);
Loader::registerNamespace('Amr\Main\Classes', 'main', 'classes' );
Loader::registerNamespace('Amr\Main\Lib', 'main', 'lib' );
Loader::registerNamespace('Amr\Main\Lib\Type', 'main', 'lib/type' );
Loader::registerNamespace('Amr\Main\Lib\Config', 'main', 'lib/config' );

// var_dump(Loader::$customNamespaces);die;

$appHttp         =Amr\Main\Lib\HttpApplication::getInstance();
$appHttp        ->initializeBasicKernel();

$app            =Amr\Main\Lib\Application::getInstance();
$app           ->initializeExtendedKernel(array(
                	"get"      => $_GET,
                	"post"     => $_POST,
                	"files"    => $_FILES,
                	"cookie"   => $_COOKIE,
                	"server"   => $_SERVER,
                	"env"      => $_ENV
                ));
            // ->initializeContext($params);

echo "<pre>";
var_dump($app->getContext());die;
