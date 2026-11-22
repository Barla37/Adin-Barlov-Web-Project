<?php
/**
 * Front controller: every request enters here (see .htaccess), FlightPHP finds the matching route.
 * Start the backend with XAMPP (Apache) or from this folder with:  php -S localhost:8000
 */
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/utils/exceptions.php';
require_once __DIR__ . '/utils/Logger.php';

// ---- Services (one shared instance each, used as Flight::user_service() ...) ----
foreach (['User', 'MembershipPlan', 'Membership', 'Trainer', 'Class', 'Booking', 'Exercise'] as $name) {
    require_once __DIR__ . "/rest/services/{$name}Service.php";
}
Flight::register('user_service', 'UserService');
Flight::register('membership_plan_service', 'MembershipPlanService');
Flight::register('membership_service', 'MembershipService');
Flight::register('trainer_service', 'TrainerService');
Flight::register('class_service', 'ClassService');
Flight::register('booking_service', 'BookingService');
Flight::register('exercise_service', 'ExerciseService');

// ---- CORS: lets the frontend (another port, e.g. VS Code Live Server) call this API ----
Flight::before('start', function () {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
});
Flight::route('OPTIONS *', function () {
    Flight::halt(204);
});

// ---- One handler for every exception thrown in a route or a service ----
Flight::map('error', function (Throwable $e) {
    // Broken rules, missing entities, external API problems: the message is written for the client
    if ($e instanceof HttpException) {
        Flight::json(['error' => $e->getMessage()], $e->get_status());
        return;
    }
    if ($e instanceof InvalidArgumentException) {
        Flight::json(['error' => $e->getMessage()], 400);
        return;
    }
    // Anything else is a bug or a database problem: the details go to the log, never to the client
    Logger::error(get_class($e) . ': ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine());
    Flight::json(['error' => 'Something went wrong on the server'], 500);
});

// A URL that matches no route
Flight::map('notFound', function () {
    Flight::json(['error' => 'Route not found'], 404);
});

// ---- Routes ----
Flight::route('GET /', function () {
    Flight::json(['name' => 'Barla Gym API', 'version' => '1.0', 'docs' => 'public/v1/docs/']);
});
foreach (['User', 'MembershipPlan', 'Membership', 'Trainer', 'Class', 'Booking', 'Exercise'] as $name) {
    require_once __DIR__ . "/rest/routes/{$name}Routes.php";
}

Flight::start();
