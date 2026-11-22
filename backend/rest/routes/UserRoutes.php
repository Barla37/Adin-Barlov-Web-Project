<?php
// User routes. A route reads the input, calls ONE service method and answers with JSON.
// Validation and business rules live in the service; errors are handled in index.php.

Flight::route('GET /users', function () {
    Flight::json(Flight::user_service()->get_all());
});

Flight::route('GET /users/@id:[0-9]+', function ($id) {
    Flight::json(Flight::user_service()->get_by_id((int) $id));
});

Flight::route('POST /users', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::user_service()->add($data), 201);
});

Flight::route('PUT|PATCH /users/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::user_service()->update($data, (int) $id));
});

Flight::route('DELETE /users/@id:[0-9]+', function ($id) {
    Flight::user_service()->delete((int) $id);
    Flight::json(['message' => 'User deleted']);
});

// Memberships and bookings of one user
Flight::route('GET /users/@id:[0-9]+/memberships', function ($id) {
    Flight::json(Flight::membership_service()->get_by_user((int) $id));
});

Flight::route('GET /users/@id:[0-9]+/bookings', function ($id) {
    Flight::json(Flight::booking_service()->get_by_user((int) $id));
});
