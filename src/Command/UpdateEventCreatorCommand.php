<?php
// src/Command/UpdateEventCreatorCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

class UpdateEventCreatorCommand extends Command
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }
    
    protected function configure()
    {
        $this->setName('app:update-event-creator')
             ->setDescription('Met à jour tous les événements existants avec createur_id = 1');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Début de la mise à jour des événements...');
        
        // Utiliser une connexion directe à la base de données
        $connection = $this->entityManager->getConnection();
        
        // Déterminer le nom exact de la table et de la colonne
        // Note: ajustez ces noms selon votre schéma de base de données
        $tableName = 'event'; // ou 'events' selon votre configuration
        $columnName = 'creator_id'; // ou 'user_id' ou autre selon votre schéma
        
        // Préparer la requête SQL
        $sql = "UPDATE $tableName SET $columnName = :creatorId WHERE $columnName IS NULL OR $columnName != :creatorId";
        
        // Exécuter la requête
        $stmt = $connection->prepare($sql);
        $count = $stmt->executeStatement(['creatorId' => 1]);
        
        $io->success(sprintf('%d événements ont été mis à jour avec le créateur ID = 1', $count));
        
        return Command::SUCCESS;
    }
}