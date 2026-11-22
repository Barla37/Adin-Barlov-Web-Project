<?php
// Class routes. A route reads the input, calls ONE service method and answers with JSON.
// Validation and business rules live in the service; errors are handled in index.php.

Flight::route('GET /classes', function () {
    Flight::json(Flight::class_service()->get_all());
});

Flight::route('GET /classes/@id:[0-9]+', function ($id) {
    Flight::json(Flight::class_service()->get_by_id((int) $id));
});

Flight::route('POST /classes', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::class_service()->add($data), 201);
});

Flight::route('PUT|PATCH /classes/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::class_service()->update($data, (int) $id));
});

Flight::route('DELETE /classes/@id:[0-9]+', function ($id) {
    Flight::class_service()->delete((int) $id);
    Flight::json(['message' => 'Class deleted']);
});

// Attendee list of one class
Flight::route('GET /classes/@id:[0-9]+/bookings', function ($id) {
    Flight::json(Flight::booking_service()->get_by_class((int) $id));
});
