<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/UserRoutes.php (one empty method per endpoint).
 * The methods are empty - they only carry the #[OA\...] attributes. Keep them in sync with the routes.
 */
class UserPaths
{
    #[OA\Get(
        path: '/users',
        tags: ['users'],
        summary: 'List all users',
        responses: [
            new OA\Response(response: 200, description: 'List of users', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/User')))
        ]
    )]
    public function getUsers() {}

    #[OA\Get(
        path: '/users/{id}',
        tags: ['users'],
        summary: 'Get one user',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'The user', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 404, description: 'User not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getUserById() {}

    #[OA\Post(
        path: '/users',
        tags: ['users'],
        summary: 'Create a user',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UserInput')),
        responses: [
            new OA\Response(response: 201, description: 'Created user', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 400, description: 'Invalid data or a business rule is broken', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function postUser() {}

    #[OA\Put(
        path: '/users/{id}',
        tags: ['users'],
        summary: 'Update a user (only the sent fields change; PATCH works the same)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UserInput')),
        responses: [
            new OA\Response(response: 200, description: 'Updated user', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 400, description: 'Invalid data or a business rule is broken', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'User not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function putUser() {}

    #[OA\Delete(
        path: '/users/{id}',
        tags: ['users'],
        summary: 'Delete a user',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'User deleted', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 400, description: 'A business rule forbids the deletion', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'User not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function deleteUser() {}

    #[OA\Get(
        path: '/users/{id}/memberships',
        tags: ['users'],
        summary: 'Memberships of one user',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of memberships', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Membership'))),
            new OA\Response(response: 404, description: 'User not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getUserMemberships() {}

    #[OA\Get(
        path: '/users/{id}/bookings',
        tags: ['users'],
        summary: 'Bookings of one user',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of bookings', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Booking'))),
            new OA\Response(response: 404, description: 'User not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getUserBookings() {}
}
