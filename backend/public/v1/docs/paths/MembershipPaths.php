<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/MembershipRoutes.php (one empty method per endpoint).
 * The methods are empty - they only carry the #[OA\...] attributes. Keep them in sync with the routes.
 */
class MembershipPaths
{
    #[OA\Get(
        path: '/memberships',
        tags: ['memberships'],
        summary: 'List all memberships',
        responses: [
            new OA\Response(response: 200, description: 'List of memberships', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Membership')))
        ]
    )]
    public function getMemberships() {}

    #[OA\Get(
        path: '/memberships/{id}',
        tags: ['memberships'],
        summary: 'Get one membership',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'The membership', content: new OA\JsonContent(ref: '#/components/schemas/Membership')),
            new OA\Response(response: 404, description: 'Membership not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getMembershipById() {}

    #[OA\Post(
        path: '/memberships',
        tags: ['memberships'],
        summary: 'Create a membership',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/MembershipInput')),
        responses: [
            new OA\Response(response: 201, description: 'Created membership', content: new OA\JsonContent(ref: '#/components/schemas/Membership')),
            new OA\Response(response: 400, description: 'Invalid data or a business rule is broken', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function postMembership() {}

    #[OA\Put(
        path: '/memberships/{id}',
        tags: ['memberships'],
        summary: 'Update a membership (only the sent fields change; PATCH works the same)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/MembershipInput')),
        responses: [
            new OA\Response(response: 200, description: 'Updated membership', content: new OA\JsonContent(ref: '#/components/schemas/Membership')),
            new OA\Response(response: 400, description: 'Invalid data or a business rule is broken', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Membership not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function putMembership() {}

    #[OA\Delete(
        path: '/memberships/{id}',
        tags: ['memberships'],
        summary: 'Delete a membership',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Membership deleted', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 400, description: 'A business rule forbids the deletion', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Membership not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function deleteMembership() {}
}
