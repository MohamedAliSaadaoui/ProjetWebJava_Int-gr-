<?php

namespace App\Tests\Controller;

use App\Entity\Demande;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DemandeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $demandeRepository;
    private string $path = '/demande/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->demandeRepository = $this->manager->getRepository(Demande::class);

        foreach ($this->demandeRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Demande index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'demande[description]' => 'Testing',
            'demande[localisation]' => 'Testing',
            'demande[categorie]' => 'Testing',
            'demande[statut]' => 'Testing',
            'demande[organisation]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->demandeRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Demande();
        $fixture->setDescription('My Title');
        $fixture->setLocalisation('My Title');
        $fixture->setCategorie('My Title');
        $fixture->setStatut('My Title');
        $fixture->setOrganisation('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Demande');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Demande();
        $fixture->setDescription('Value');
        $fixture->setLocalisation('Value');
        $fixture->setCategorie('Value');
        $fixture->setStatut('Value');
        $fixture->setOrganisation('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'demande[description]' => 'Something New',
            'demande[localisation]' => 'Something New',
            'demande[categorie]' => 'Something New',
            'demande[statut]' => 'Something New',
            'demande[organisation]' => 'Something New',
        ]);

        self::assertResponseRedirects('/demande/');

        $fixture = $this->demandeRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getLocalisation());
        self::assertSame('Something New', $fixture[0]->getCategorie());
        self::assertSame('Something New', $fixture[0]->getStatut());
        self::assertSame('Something New', $fixture[0]->getOrganisation());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Demande();
        $fixture->setDescription('Value');
        $fixture->setLocalisation('Value');
        $fixture->setCategorie('Value');
        $fixture->setStatut('Value');
        $fixture->setOrganisation('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/demande/');
        self::assertSame(0, $this->demandeRepository->count([]));
    }
}
