<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Person;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class PersonTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/people');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Person',
            '@id' => '/people',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/people?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/people?page=1',
                'hydra:last' => '/people?page=4',
                'hydra:next' => '/people?page=2',
            ],
        ]);

        $this->assertCount(30, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Person::class);
    }

    public function testCreatePerson(): void
    {
        $response = static::createClient()->request('POST', '/people', ['json' => [
            'email' => 'test@test.pl',
            'fullname' => 'Krzysztof Kosman',
            'birthday' => '1988-03-26T00:00:00+00:00',
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/contexts/Person',
            '@type' => 'Person',
            'email' => 'test@test.pl',
            'fullname' => 'Krzysztof Kosman',
            'birthday' => '1988-03-26T00:00:00+00:00',
            'weights' => [],
        ]);
        $this->assertMatchesRegularExpression('~^/people/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Person::class);
    }

    public function testCreateInvalidPerson(): void
    {
        static::createClient()->request('POST', '/people', ['json' => [
            'email' => 'this is wrong',
            'birthday' => '1188-03-26T00:00:00+00:00',
        ]]);

        $this->assertResponseStatusCodeSame(500);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Person cannot be older than 120 years.',
        ]);
    }

    public function testUpdatePerson(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Person::class, ['fullname' => 'Krzysztof Kosman']);

        $client->request('PUT', $iri, ['json' => [
            'fullname' => 'Elon Musk',
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'email' => 'test@test.pl',
            'fullname' => 'Elon Musk',
        ]);
    }

    public function testDeletePerson(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Person::class, ['fullname' => 'Krzysztof Kosman']);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::$container->get('doctrine')->getRepository(Person::class)->findOneBy(['fullname' => 'Krzysztof Kosman'])
        );
    }
}