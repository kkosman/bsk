<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Training;
use App\Entity\Person;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class TrainingTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/trainings');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Training',
            '@id' => '/trainings',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/trainings?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/trainings?page=1',
                'hydra:last' => '/trainings?page=4',
                'hydra:next' => '/trainings?page=2',
            ],
        ]);

        $this->assertCount(30, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Training::class);
    }

    public function testCreateTraining(): void
    {
        $client = static::createClient();
        $person_iri = $this->findIriBy(Person::class, ['fullname' => 'Krzysztof Kosman']);

        $response = static::createClient()->request('POST', '/trainings', ['json' => [
            'description' => 'Lorem ipsum dolor sit',
            'type' => 'kondycyjny',
            'duration' => 55,
            'person' => $person_iri,
            'date' => '2022-05-01T00:00:00+00:00',
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/contexts/Training',
            '@type' => 'Training',
            'description' => 'Lorem ipsum dolor sit',
            'type' => 'kondycyjny',
            'duration' => 55,
            'person' => $person_iri,
            'date' => '2022-05-01T00:00:00+00:00',
        ]);
        $this->assertMatchesRegularExpression('~^/trainings/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Training::class);
    }

    public function testCreateInvalidTraining(): void
    {
        $client = static::createClient();
        $person_iri = $this->findIriBy(Person::class, ['fullname' => 'Krzysztof Kosman']);

        $client->request('POST', '/trainings', ['json' => [
            'description' => 'no',
            'type' => 'no',
            'duration' => 55,
            'person' => $person_iri,
            'date' => '2022-05-01T00:00:00+00:00',
        ]]);

        $this->assertResponseStatusCodeSame(500);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'This training type is not allowed!',
        ]);
    }

    public function testUpdateTraining(): void
    {
        $client = static::createClient();
        $person = $this->findIriBy(Person::class, ['fullname' => 'Krzysztof Kosman']);
        $person = explode("/",$person);
        $person = end($person);
        $iri = $this->findIriBy(Training::class, ['person' => $person]);

        $client->request('PUT', $iri, ['json' => [
            'description' => 'change change change',
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'description' => 'change change change',
        ]);
    }

    public function testDeleteTraining(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Training::class, ['description' => 'test training test']);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::$container->get('doctrine')->getRepository(Training::class)->findOneBy(['description' => 'test training test'])
        );
    }
}