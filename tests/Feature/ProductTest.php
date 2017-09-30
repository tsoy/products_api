<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProductTest extends TestCase
{
    use DatabaseMigrations;

    private $user;

    private function getHeaders()
    {
        if (!$this->user) {
            $this->user = factory(\App\User::class)->create();
        }

        return ['Authorization' => "Bearer {$this->user->api_token}"];
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testProductCRUD()
    {

// Create
// ----------------------------------------------

        $response = $this->json(
            'POST',
            '/api/products',
            [
                'name'        => 'Bose',
                'description' => 'Exclusive waveguide speaker technology delivers high-performance sound',
            ],
            $this->getHeaders()
        );


        $response
            ->assertStatus(422)
            ->assertJson([
                'price' => ["The price field is required."],
            ]);


        $response = $this->json(
            'POST',
            '/api/products',
            [
                'name'        => 'Bose',
                'description' => 'Exclusive waveguide speaker technology delivers high-performance sound',
                'price'       => 4.99,
            ],
            $this->getHeaders()
        );


        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => ["name" => 'Bose'],
            ]);


// ----------------------------------------------

        $response = $this->json(
            'POST',
            '/api/products',
            [
                'name'        => 'Sonos',
                'description' => 'The mid-size home speaker with stereo sound.',
                'price'       => 4.99,
            ],
            $this->getHeaders()
        );


        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => ["name" => 'Sonos'],
            ]);

        $sonos_id = array_get($response->json(), 'data.id');


// Update
// ----------------------------------------------

        $response = $this->json(
            'POST',
            "/api/products/{$sonos_id}",
            [
                'price' => 14.99,
            ],
            $this->getHeaders()
        );


        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => ["name" => 'Sonos', "price" => 14.99],
            ]);

        $response = $this->json(
            'POST',
            "/api/products/{$sonos_id}",
            [
                'price' => "price",
            ],
            $this->getHeaders()
        );


        $response
            ->assertStatus(422)
            ->assertJson([
                "price" => [
                    "The price must be a number.",
                ]]);


// List all Products
// ----------------------------------------------

        $response = $this->json(
            'GET',
            '/api/products',
            $this->getHeaders()
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ["name" => 'Bose'],
                    ["id" => $sonos_id, "name" => 'Sonos'],
                ],
            ]);

// Attach to user
// ----------------------------------------------

        $response = $this->json(
            'POST',
            '/api/user/products',
            [
                'id' => $sonos_id,
            ],
            $this->getHeaders()
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'Product was attached',
            ]);


        // ----------------------------------------------

        $response = $this->json(
            'POST',
            '/api/user/products',
            [
                'id' => $sonos_id,
            ],
            $this->getHeaders()
        );

        $response
            ->assertStatus(400)
            ->assertJson([
                'Product is already attached to the user.',
            ]);


// List user's products
// ----------------------------------------------

        $response = $this->json(
            'GET',
            '/api/user/products',
            $this->getHeaders()
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ["id" => $sonos_id, "name" => 'Sonos'],
                ],
            ]);

// Detach from user
// ----------------------------------------------

        $response = $this->json(
            'DELETE',
            '/api/user/products',
            [
                'id' => $sonos_id,
            ],
            $this->getHeaders()
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'Product was detached',
            ]);


// ----------------------------------------------

        $response = $this->json(
            'DELETE',
            '/api/user/products',
            [
                'id' => $sonos_id,
            ],
            $this->getHeaders()
        );

        $response
            ->assertStatus(400)
            ->assertJson([
                'Product is not attached to the user.',
            ]);

// Delete product
// ----------------------------------------------

        $response = $this->json(
            'DELETE',
            "/api/products/$sonos_id",
            $this->getHeaders()
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'Product was deleted',
            ]);

        $this->assertDatabaseMissing('products', ['id' => $sonos_id]);

    }


}
