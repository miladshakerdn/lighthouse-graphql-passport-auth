<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Http\Middleware;

use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;

class AuthenticateWithApiGuard extends TestCase
{
    public function test_it_sets_user_via_global_middleware()
    {
        $this->createClient();
        $user = User::create([
            'name'     => 'Jose Fonseca',
            'email'    => 'jose@example.com',
            'password' => bcrypt('123456789qq'),
        ]);
        $response = $this->postGraphQL([
            'query' => 'mutation {
                login(input: {
                    username: "jose@example.com",
                    password: "123456789qq"
                }) {
                    access_token
                    refresh_token
                    user {
                        id
                        name
                        email
                    }
                }
            }',
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $access_token = $responseBody['data']['login']['access_token'];
        $response = $this->postGraphQL([
            'query' => '{
                loggedInUserViaGuardForTest {
                    id
                    name
                    email
                }
            }',
        ], [
            'Authorization' => 'Bearer '.$access_token,
        ]);
        $response->assertJson([
            'data' => [
                'loggedInUserViaGuardForTest' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ],
        ]);
    }
}
