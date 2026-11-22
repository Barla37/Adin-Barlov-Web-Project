<?php
// Booking routes. A route reads the input, calls ONE service method and answers with JSON.
// Validation and business rules live in the service; errors are handled in index.php.

// GET /bookings                 all bookings
// GET /bookings?user_id=2       bookings of one user
// GET /bookings?class_id=6      bookings of one class
Flight::route('GET /bookings', function () {
    $query = Flight::request()->query;
    if (isset($query['user_id'])) {
        Flight::json(Flight::booking_service()->get_by_user((int) $query['user_id']));
    } elseif (isset($query['class_id'])) {
        Flight::json(Flight::booking_service()->get_by_class((int) $query['class_id']));
    } else {
        Flight::json(Flight::booking_service()->get_all());
    }
});

Flight::route('GET /bookings/@id:[0-9]+', function ($id) {
    Flight::json(Flight::booking_service()->get_by_id((int) $id));
});

Flight::route('POST /bookings', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::booking_service()->add($data), 201);
});

Flight::route('PUT|PATCH /bookings/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::booking_service()->update($data, (int) $id));
});

Flight::route('DELETE /bookings/@id:[0-9]+', function ($id) {
    Flight::booking_service()->delete((int) $id);
    Flight::json(['message' => 'Booking deleted']);
});
