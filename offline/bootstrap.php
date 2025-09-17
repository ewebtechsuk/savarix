<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require __DIR__ . '/Routing/RouteCollector.php';
require __DIR__ . '/Routing/RouteDefinition.php';
require __DIR__ . '/Routing/RouteGroupBuilder.php';
require __DIR__ . '/Routing/ResourceRegistration.php';
require __DIR__ . '/Routing/RouteFacade.php';
require __DIR__ . '/Support/Table.php';
require __DIR__ . '/Support/Env.php';
require __DIR__ . '/Support/RouteLoader.php';
