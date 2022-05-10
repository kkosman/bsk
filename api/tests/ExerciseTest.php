<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Exercise;
use App\Entity\Person;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class ExerciseTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    // public function testGetCollection(): void
    // {
    //     $response = static::createClient()->request('GET', '/exercises');

    //     $this->assertResponseIsSuccessful();
    //     $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

    //     $this->assertJsonContains([
    //         '@context' => '/contexts/Exercise',
    //         '@id' => '/exercises',
    //         '@type' => 'hydra:Collection',
    //         'hydra:totalItems' => 100,
    //         'hydra:view' => [
    //             '@id' => '/exercises?page=1',
    //             '@type' => 'hydra:PartialCollectionView',
    //             'hydra:first' => '/exercises?page=1',
    //             'hydra:last' => '/exercises?page=4',
    //             'hydra:next' => '/exercises?page=2',
    //         ],
    //     ]);

    //     $this->assertCount(30, $response->toArray()['hydra:member']);
    //     $this->assertMatchesResourceCollectionJsonSchema(Exercise::class);
    // }

    // public function testCreateExercise(): void
    // {
    //     $client = static::createClient();
    //     $person_iri = $iri = $this->findIriBy(Person::class, ['fullname' => 'Krzysztof Kosman']);

    //     $response = static::createClient()->request('POST', '/exercises', ['json' => [
    //         'Exercise' => 55,
    //         'person' => $person_iri,
    //         'date' => '2022-03-26T00:00:00+00:00',
    //     ]]);

    //     $this->assertResponseStatusCodeSame(201);
    //     $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    //     $this->assertJsonContains([
    //         '@context' => '/contexts/Exercise',
    //         '@type' => 'Exercise',
    //         'Exercise' => 55,
    //         'person' => $person_iri,
    //         'date' => '2022-03-26T00:00:00+00:00',
    //     ]);
    //     $this->assertMatchesRegularExpression('~^/exercises/\d+$~', $response->toArray()['@id']);
    //     $this->assertMatchesResourceItemJsonSchema(Exercise::class);
    // }

    // public function testCreateInvalidExercise(): void
    // {
    //     static::createClient()->request('POST', '/exercises', ['json' => [
    //         'email' => 'this is wrong',
    //         'birthday' => '1188-03-26T00:00:00+00:00',
    //     ]]);

    //     $this->assertResponseStatusCodeSame(500);
    //     $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

    //     $this->assertJsonContains([
    //         '@context' => '/contexts/Error',
    //         '@type' => 'hydra:Error',
    //         'hydra:title' => 'An error occurred',
    //         'hydra:description' => 'Exercise cannot be older than 120 years.',
    //     ]);
    // }

    // public function testUpdateExercise(): void
    // {
    //     $client = static::createClient();
    //     $iri = $this->findIriBy(Exercise::class, ['fullname' => 'Krzysztof Kosman']);

    //     $client->request('PUT', $iri, ['json' => [
    //         'fullname' => 'Elon Musk',
    //     ]]);

    //     $this->assertResponseIsSuccessful();
    //     $this->assertJsonContains([
    //         '@id' => $iri,
    //         'email' => 'test@test.pl',
    //         'fullname' => 'Elon Musk',
    //     ]);
    // }

    // public function testDeleteExercise(): void
    // {
    //     $client = static::createClient();
    //     $iri = $this->findIriBy(Exercise::class, ['fullname' => 'Krzysztof Kosman']);

    //     $client->request('DELETE', $iri);

    //     $this->assertResponseStatusCodeSame(204);
    //     $this->assertNull(
    //         static::$container->get('doctrine')->getRepository(Exercise::class)->findOneBy(['fullname' => 'Krzysztof Kosman'])
    //     );
    // }
}