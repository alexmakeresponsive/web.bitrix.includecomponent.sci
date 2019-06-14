<?php

define("LANG",          's1');
define("SITE_ID",       's1');
define("SITE_DIR",      '/');
define("LANG_DIR",      '/');
define("LANGUAGE_ID",   'ru');
define("SITE_TEMPLATE_ID",   'twbs4_1');

require_once $_SERVER['DOCUMENT_ROOT'] . '/loader.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/amr/modules/main/tools.php';

\spl_autoload_register([Loader::class, 'autoLoad']);

Loader::switchAutoLoad(true);
Loader::registerNamespace('Amr\Main\Classes', 'main', 'classes' );
Loader::registerNamespace('Amr\Main\Classes\General', 'main', 'classes/general' );
Loader::registerNamespace('Amr\Main\Classes\Mysql', 'main', 'classes/mysql' );
Loader::registerNamespace('Amr\Main\Classes\Tools', 'main', 'classes/tools' );
Loader::registerNamespace('Amr\Main\Lib', 'main', 'lib' );
Loader::registerNamespace('Amr\Main\Lib\Type', 'main', 'lib/type' );
Loader::registerNamespace('Amr\Main\Lib\Config', 'main', 'lib/config' );
Loader::registerNamespace('Amr\Main\Lib\Page', 'main', 'lib/page' );
Loader::registerNamespace('Amr\Main\Lib\Localization', 'main', 'lib/localization' );
Loader::registerNamespace('Amr\Main\Lib\Io', 'main', 'lib/io' );

// var_dump(Loader::$customNamespaces);die;

$appHttp         =Amr\Main\Lib\HttpApplication::getInstance();
$appHttp        ->initializeBasicKernel();

$app             =Amr\Main\Lib\Application::getInstance();
$app            ->initializeExtendedKernel(array(
                	"get"      => $_GET,
                	"post"     => $_POST,
                	"files"    => $_FILES,
                	"cookie"   => $_COOKIE,
                	"server"   => $_SERVER,
                	"env"      => $_ENV
                ));
            // ->initializeContext($params);

$main           =new Amr\Main\Classes\Mysql\CMain;
$main          ->IncludeComponent(
                        "amr:menu",
                        "bottom_menu",
                        array(
                            "ROOT_MENU_TYPE" => "bottom",
                            "MAX_LEVEL" => "1",
                            "MENU_CACHE_TYPE" => "A",
                            "CACHE_SELECTED_ITEMS" => "N",
                            "MENU_CACHE_TIME" => "36000000",
                            "MENU_CACHE_USE_GROUPS" => "Y",
                            "MENU_CACHE_GET_VARS" => array(
                            ),
                        ),
                        false   // parent component
                );
