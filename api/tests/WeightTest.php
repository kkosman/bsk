<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Weight;
use App\Entity\Person;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class WeightTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/weights');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Weight',
            '@id' => '/weights',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/weights?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/weights?page=1',
                'hydra:last' => '/weights?page=4',
                'hydra:next' => '/weights?page=2',
            ],
        ]);

        $this->assertCount(30, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Weight::class);
    }

    public function testCreateWeight(): void
    {
        $client = static::createClient();
        $person_iri = $this->findIriBy(Person::class, ['fullname' => 'Krzysztof Kosman']);

        $response = static::createClient()->request('POST', '/weights', ['json' => [
            'weight' => 55,
            'person' => $person_iri,
            'date' => '2022-03-26T00:00:00+00:00',
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/contexts/Weight',
            '@type' => 'Weight',
            'weight' => 55,
            'person' => $person_iri,
            'date' => '2022-03-26T00:00:00+00:00',
        ]);
        $this->assertMatchesRegularExpression('~^/weights/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Weight::class);
    }

    public function testCreateInvalidDateWeight(): void
    {
        $client = static::createClient();
        $person_iri = $this->findIriBy(Person::class, ['fullname' => 'Krzysztof Kosman']);

        $client->request('POST', '/weights', ['json' => [
            'weight' => 155,
            'person' => $person_iri,
            'date' => '1199-03-26T00:00:00+00:00',
        ]]);

        $this->assertResponseStatusCodeSame(500);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Weight record date is too far in history!',
        ]);
    }

    public function testCreateInvalidWeightLessThan3UpperBound(): void
    {
        $client = static::createClient();
        $person_iri = $this->findIriBy(Person::class, ['fullname' => 'Krzysztof Kosman']);

        $client->request('POST', '/weights', ['json' => [
            'weight' => 201,
            'person' => $person_iri,
            'date' => '2022-03-26T00:00:00+00:00',
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
        ]);
    }

    public function testCreateInvalidWeightLessThan3LowerBound(): void
    {
        $client = static::createClient();
        $person_iri = $this->findIriBy(Person::class, ['fullname' => 'Krzysztof Kosman']);

        $client->request('POST', '/weights', ['json' => [
            'weight' => 5,
            'person' => $person_iri,
            'date' => '2022-03-26T00:00:00+00:00',
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
        ]);
    }

    public function testCreateInvalidWeightStdDeviation(): void
    {
        $client = static::createClient();
        $person_iri = $this->findIriBy(Person::class, ['fullname' => 'Jakub Probny']);

        $client->request('POST', '/weights', ['json' => [
            'weight' => 155,
            'person' => $person_iri,
            'date' => '2022-03-26T00:00:00+00:00',
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
        ]);
    }

    public function testCreateInvalidWeightNoProposition(): void
    {
        $client = static::createClient();
        $person_iri = $this->findIriBy(Person::class, ['fullname' => 'Jakub Probny']);

        $client->request('POST', '/weights', ['json' => [
            'weight' => 90,
            'person' => $person_iri,
            'date' => '2022-03-26T00:00:00+00:00',
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
        ]);
    }

    public function testUpdateWeight(): void
    {
        $client = static::createClient();
        $person = $this->findIriBy(Person::class, ['fullname' => 'Krzysztof Kosman']);
        $person = explode("/",$person);
        $person = end($person);
        $iri = $this->findIriBy(Weight::class, ['person' => $person]);

        $client->request('PUT', $iri, ['json' => [
            'weight' => 59,
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'weight' => 59,
        ]);
    }

    public function testDeleteWeight(): void
    {
        $client = static::createClient();
        $person = $this->findIriBy(Person::class, ['fullname' => 'Krzysztof Kosman']);
        $person = explode("/",$person);
        $person = end($person);
        $iri = $this->findIriBy(Weight::class, ['person' => $person]);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::$container->get('doctrine')->getRepository(Weight::class)->findOneBy(['person' => $person])
        );
    }
}