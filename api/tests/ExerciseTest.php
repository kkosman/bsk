<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Exercise;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class ExerciseTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/exercises');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Exercise',
            '@id' => '/exercises',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 10,
        ]);

        $this->assertCount(10, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Exercise::class);
    }

    public function testCreateExercise(): void
    {
        $client = static::createClient();
        $response = $client->request('POST', '/exercises', ['json' => [
            'calories' => 55,
            'name' => 'lifting',
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/contexts/Exercise',
            '@type' => 'Exercise',
            'calories' => 55,
            'name' => 'lifting',
        ]);
        $this->assertMatchesRegularExpression('~^/exercises/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Exercise::class);
    }

    public function testCreateInvalidExercise(): void
    {
        static::createClient()->request('POST', '/exercises', ['json' => [
            'calories' => 55,
            'name' => 'a',
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'name: Exercise name must be at least 3 characters long.',
        ]);
    }

    public function testUpdateExercise(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Exercise::class, ['name' => 'lifting']);

        $client->request('PUT', $iri, ['json' => [
            'name' => 'running',
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'name' => 'running',
        ]);
    }

    public function testDeleteExercise(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Exercise::class, ['name' => 'lifting']);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::$container->get('doctrine')->getRepository(Exercise::class)->findOneBy(['name' => 'lifting'])
        );
    }
}