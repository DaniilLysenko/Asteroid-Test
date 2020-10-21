<?php

namespace App\Tests\Controller\Api;

use App\Tests\AppTestCase;
use Symfony\Component\HttpFoundation\Response;

class NeoAsteroidControllerTest extends AppTestCase
{
    public function testHazardous()
    {
        $this->client->request('GET', '/api/neo/hazardous');

        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $result = json_decode($this->client->getResponse()->getContent());

        static::assertObjectHasAttribute('asteroids', $result);
        static::assertIsArray($result->asteroids);
        static::assertCount(2, $result->asteroids);

        $asteroid = reset($result->asteroids);
        $this->assertObjectHasAttributes($asteroid, '$asteroid', [
            'id', 'date', 'reference', 'name', 'speed', 'hazardous',
        ]);
    }

    public function testHazardousMethodNotAllowed()
    {
        $this->client->request('POST', '/api/neo/hazardous');

        static::assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode());
    }

    public function testFastestWithNoHazardous()
    {
        $this->client->request('GET', '/api/neo/fastest');

        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $result = json_decode($this->client->getResponse()->getContent());

        static::assertObjectHasAttribute('asteroid', $result);
        static::assertIsObject($result->asteroid);

        $this->assertObjectHasAttributes($result->asteroid, '$result->asteroid', [
            'id', 'date', 'reference', 'name', 'speed', 'hazardous',
        ]);

        static::assertFalse($result->asteroid->hazardous);
        static::assertSame(385.76, $result->asteroid->speed);
    }

    public function testFastestWithHazardous()
    {
        $this->client->request('GET', '/api/neo/fastest?hazardous=true');

        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $result = json_decode($this->client->getResponse()->getContent());

        static::assertObjectHasAttribute('asteroid', $result);
        static::assertIsObject($result->asteroid);

        $this->assertObjectHasAttributes($result->asteroid, '$result->asteroid', [
            'id', 'date', 'reference', 'name', 'speed', 'hazardous',
        ]);

        static::assertTrue($result->asteroid->hazardous);
        static::assertSame(675.12, $result->asteroid->speed);
    }

    public function testFastestMethodNotAllowed()
    {
        $this->client->request('POST', '/api/neo/fastest?hazardous=true');

        static::assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode());
    }
}