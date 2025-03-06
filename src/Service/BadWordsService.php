<?php

namespace App\Service;

class BadWordsService
{
    private $badWords = [];
    private $filePath;

    public function __construct(string $projectDir)
    {
        // Chemin vers le fichier des mots inappropriés
        $this->filePath = $projectDir . '/data/fr.txt';
        $this->loadBadWords();
    }

    /**
     * Charge la liste des mots inappropriés depuis le fichier
     */
    private function loadBadWords(): void
    {
        if (file_exists($this->filePath)) {
            $content = file_get_contents($this->filePath);
            $this->badWords = array_filter(array_map('trim', explode("\n", $content)));
        }
    }

    /**
     * Vérifie si le texte contient des mots inappropriés
     */
    public function containsBadWords(string $text): array
    {
        $result = [
            'containsBadWords' => false,
            'badWords' => []
        ];

        // Convertir le texte en minuscules pour une comparaison insensible à la casse
        $textLowercase = mb_strtolower($text, 'UTF-8');

        foreach ($this->badWords as $word) {
            // Utiliser une expression régulière pour trouver le mot entier
            // \b assure que nous trouvons des mots entiers et pas des sous-chaînes
            $pattern = '/\b' . preg_quote($word, '/') . '\b/ui';

            if (preg_match($pattern, $textLowercase)) {
                $result['containsBadWords'] = true;
                $result['badWords'][] = $word;
            }
        }

        return $result;
    }
}