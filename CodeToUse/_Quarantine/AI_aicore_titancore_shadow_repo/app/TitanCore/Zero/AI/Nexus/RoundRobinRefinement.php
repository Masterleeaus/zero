<?php

namespace App\TitanCore\Zero\AI\Nexus;

class RoundRobinRefinement
{
    public function refine(array $votes): array
    {
        foreach ($votes as $index => $vote) {
            $votes[$index]['refined'] = true;
            $votes[$index]['refinement_note'] = 'Round-robin refinement applied.';
        }

        return $votes;
    }
}
