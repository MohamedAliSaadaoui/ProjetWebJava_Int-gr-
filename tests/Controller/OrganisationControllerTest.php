<?php

namespace App\Test\Controller;

use App\Entity\Organisation;
use App\Repository\OrganisationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrganisationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private OrganisationRepository $repository;
    private string $path = '/organisation/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Organisation::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Organisation index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'organisation[nom]' => 'Testing',
            'organisation[email]' => 'Testing',
            'organisation[description]' => 'Testing',
            'organisation[telephone]' => 'Testing',
            'organisation[adresse]' => 'Testing',
        ]);

        self::assertResponseRedirects('/organisation/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Organisation();
        $fixture->setNom('My Title');
        $fixture->setEmail('My Title');
        $fixture->setDescription('My Title');
        $fixture->setTelephone('My Title');
        $fixture->setAdresse('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Organisation');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Organisation();
        $fixture->setNom('My Title');
        $fixture->setEmail('My Title');
        $fixture->setDescription('My Title');
        $fixture->setTelephone('My Title');
        $fixture->setAdresse('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'organisation[nom]' => 'Something New',
            'organisation[email]' => 'Something New',
            'organisation[description]' => 'Something New',
            'organisation[telephone]' => 'Something New',
            'organisation[adresse]' => 'Something New',
        ]);

        self::assertResponseRedirects('/organisation/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getTelephone());
        self::assertSame('Something New', $fixture[0]->getAdresse());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Organisation();
        $fixture->setNom('My Title');
        $fixture->setEmail('My Title');
        $fixture->setDescription('My Title');
        $fixture->setTelephone('My Title');
        $fixture->setAdresse('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/organisation/');
    }
}
