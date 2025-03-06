<?php
/// src/Command/FixParticipeUserCommand.php
namespace App\Command;

use App\Entity\Participe;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fix-participe-user',
    description: 'Fixes invalid user references in Participe entity',
)]
class FixParticipeUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting to fix Participe entities with invalid user_id');
        
        // Récupérer l'utilisateur avec ID = 1
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find(1);
        
        if (!$user) {
            $output->writeln('User with ID 1 not found! Please check your database.');
            return Command::FAILURE;
        }
        
        $participesWithInvalidUser = $this->entityManager->createQuery(
            'SELECT p FROM App\Entity\Participe p WHERE p.user = 0'
        )->getResult();
        
        $count = count($participesWithInvalidUser);
        $output->writeln("Found {$count} Participe entities with user_id = 0");
        
        foreach ($participesWithInvalidUser as $participe) {
            $participe->setUser($user);
            $output->writeln("Set user to user_id=1 for Participe with ID: " . $participe->getId());
        }
        
        $this->entityManager->flush();
        $output->writeln('All invalid user references have been fixed');
        
        return Command::SUCCESS;
    }
}