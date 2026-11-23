<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/BookingRoutes.php (one empty method per endpoint).
 * The methods are empty - they only carry the #[OA\...] attributes. Keep them in sync with the routes.
 */
class BookingPaths
{
    #[OA\Get(
        path: '/bookings',
        tags: ['bookings'],
        summary: 'List bookings (optionally of one user or one class)',
        parameters: [
            new OA\Parameter(name: 'user_id', in: 'query', required: false, description: 'Only bookings of this user', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'class_id', in: 'query', required: false, description: 'Only bookings of this class', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of bookings', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Booking')))
        ]
    )]
    public function getBookings() {}

    #[OA\Get(
        path: '/bookings/{id}',
        tags: ['bookings'],
        summary: 'Get one booking',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'The booking', content: new OA\JsonContent(ref: '#/components/schemas/Booking')),
            new OA\Response(response: 404, description: 'Booking not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getBookingById() {}

    #[OA\Post(
        path: '/bookings',
        tags: ['bookings'],
        summary: 'Create a booking',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/BookingInput')),
        responses: [
            new OA\Response(response: 201, description: 'Created booking (status is always confirmed)', content: new OA\JsonContent(ref: '#/components/schemas/Booking')),
            new OA\Response(response: 400, description: 'Invalid data or a business rule is broken', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function postBooking() {}

    #[OA\Put(
        path: '/bookings/{id}',
        tags: ['bookings'],
        summary: 'Change the status of a booking (confirmed, cancelled, attended)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/BookingStatus')),
        responses: [
            new OA\Response(response: 200, description: 'Updated booking', content: new OA\JsonContent(ref: '#/components/schemas/Booking')),
            new OA\Response(response: 400, description: 'Invalid data or a business rule is broken', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Booking not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function putBooking() {}

    #[OA\Delete(
        path: '/bookings/{id}',
        tags: ['bookings'],
        summary: 'Delete a booking',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Booking deleted', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 400, description: 'A business rule forbids the deletion', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Booking not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function deleteBooking() {}
}
