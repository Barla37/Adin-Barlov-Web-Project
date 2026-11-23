<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/ClassRoutes.php (one empty method per endpoint).
 * The methods are empty - they only carry the #[OA\...] attributes. Keep them in sync with the routes.
 */
class GymClassPaths
{
    #[OA\Get(
        path: '/classes',
        tags: ['classes'],
        summary: 'List all classes',
        responses: [
            new OA\Response(response: 200, description: 'List of classes', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/GymClass')))
        ]
    )]
    public function getGymClasss() {}

    #[OA\Get(
        path: '/classes/{id}',
        tags: ['classes'],
        summary: 'Get one class',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'The class', content: new OA\JsonContent(ref: '#/components/schemas/GymClass')),
            new OA\Response(response: 404, description: 'Class not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getGymClassById() {}

    #[OA\Post(
        path: '/classes',
        tags: ['classes'],
        summary: 'Create a class',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/GymClassInput')),
        responses: [
            new OA\Response(response: 201, description: 'Created class', content: new OA\JsonContent(ref: '#/components/schemas/GymClass')),
            new OA\Response(response: 400, description: 'Invalid data or a business rule is broken', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function postGymClass() {}

    #[OA\Put(
        path: '/classes/{id}',
        tags: ['classes'],
        summary: 'Update a class (only the sent fields change; PATCH works the same)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/GymClassInput')),
        responses: [
            new OA\Response(response: 200, description: 'Updated class', content: new OA\JsonContent(ref: '#/components/schemas/GymClass')),
            new OA\Response(response: 400, description: 'Invalid data or a business rule is broken', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Class not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function putGymClass() {}

    #[OA\Delete(
        path: '/classes/{id}',
        tags: ['classes'],
        summary: 'Delete a class',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Class deleted', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 400, description: 'A business rule forbids the deletion', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Class not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function deleteGymClass() {}

    #[OA\Get(
        path: '/classes/{id}/bookings',
        tags: ['classes'],
        summary: 'Attendee list of one class',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of bookings', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Booking'))),
            new OA\Response(response: 404, description: 'Class not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getClassBookings() {}
}
