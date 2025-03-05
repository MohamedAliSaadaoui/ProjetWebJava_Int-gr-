<?php

namespace App\DataFixtures;

use App\Entity\Livraison;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LivraisonFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $livraisonStandard = new Livraison();
        $livraisonStandard->setNom('Livraison Standard');
        $livraisonStandard->setTarif(4.99);
        $livraisonStandard->setDelai('3-5 jours ouvrés');
        $manager->persist($livraisonStandard);
        
        $livraisonExpress = new Livraison();
        $livraisonExpress->setNom('Livraison Express');
        $livraisonExpress->setTarif(9.99);
        $livraisonExpress->setDelai('1-2 jours ouvrés');
        $manager->persist($livraisonExpress);
        
        $livraisonRelais = new Livraison();
        $livraisonRelais->setNom('Point Relais');
        $livraisonRelais->setTarif(3.50);
        $livraisonRelais->setDelai('3-4 jours ouvrés');
        $manager->persist($livraisonRelais);
        
        $manager->flush();
    }
} 