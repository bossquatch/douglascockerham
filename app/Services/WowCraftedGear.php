<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class WowCraftedGear
{
    private const LEATHER_CLASSES = [1, 2, 3, 4, 7, 11];

    // Relative leveling preferences, not a combat simulation.
    private const BALANCE_WEIGHTS = [
        'Spell Hit' => 5, 'Spell Power' => 4, 'Arcane Spell Power' => 4, 'Nature Spell Power' => 4,
        'Intellect' => 3, 'Spell Critical Strike' => 2, 'Spell Haste' => 2, 'Spirit' => 1,
        'Stamina' => 0.75, 'Armor' => 0.05, 'Agility' => 0.1, 'Strength' => 0.1,
    ];

    public function compare(array $target, array $equipment, bool $balance = false): array
    {
        $level = $target['level'] ?? null;
        if (! is_int($level) || $level < 1 || $equipment === []) {
            return [];
        }

        $snapshot = DB::table('wow_character_snapshots')->where('character', 'Darvor')
            ->where('realm', 'Nightstorm')->orderByDesc('id')->value('id');
        if (! $snapshot) {
            return [];
        }

        $known = DB::table('wow_learned_recipes')->where('snapshot_id', $snapshot)
            ->where('profession', 'Leatherworking')->pluck('name')->flip();
        $canWearLeather = in_array($target['character_class']['id'] ?? null, self::LEATHER_CLASSES, true);
        $offers = [];
        foreach ($equipment as $slot => $equipped) {
            if (! is_array($equipped)) {
                continue;
            }
            $type = isset($equipped['slot_type']) ? ucfirst(strtolower($equipped['slot_type'])) : $slot;
            if ($type !== 'Back' && ! $canWearLeather) {
                continue;
            }
            $candidates = config('wow.offerable_gear')[$type] ?? [];
            [$current, $specialEffect] = $this->equippedStats($equipped);
            if ($current === []) {
                continue;
            }

            foreach ($candidates as [$name, $id, $requiredLevel, $stats]) {
                if (! $known->has($name) || $requiredLevel > $level + 5 || (int) ($equipped['id'] ?? 0) === $id) {
                    continue;
                }

                $deltas = [];
                foreach (array_keys($stats + $current) as $stat) {
                    if ($stat === 'Armor' && ! isset($current['Armor'])) {
                        continue;
                    }
                    $delta = ($stats[$stat] ?? 0) - ($current[$stat] ?? 0);
                    if ($delta !== 0) {
                        $deltas[$stat] = $delta;
                    }
                }

                if (! array_filter($deltas, fn ($delta) => $delta > 0)) {
                    continue;
                }

                $score = null;
                if ($balance) {
                    $score = 0;
                    foreach ($deltas as $stat => $delta) {
                        $score += $delta * (self::BALANCE_WEIGHTS[$stat] ?? 0);
                    }
                }

                $offers[$slot][] = [
                    'name' => $name,
                    'id' => $id,
                    'level_needed' => $requiredLevel > $level ? $requiredLevel : null,
                    'deltas' => $deltas,
                    'tradeoff' => $specialEffect || (bool) array_filter($deltas, fn ($delta) => $delta < 0),
                    'special_effect' => $specialEffect,
                    'balance_score' => $score,
                ];
            }

            if (isset($offers[$slot])) {
                usort($offers[$slot], fn ($a, $b) => [$a['level_needed'] ?? 0, -($a['balance_score'] ?? 0), $a['tradeoff']] <=> [$b['level_needed'] ?? 0, -($b['balance_score'] ?? 0), $b['tradeoff']]);
            }
        }

        return $offers;
    }

    private function equippedStats(array $equipment): array
    {
        $stats = [];
        $specialEffect = false;
        if (is_numeric($equipment['armor'] ?? null)) {
            $stats['Armor'] = (int) $equipment['armor'];
        }
        foreach (($equipment['lines'] ?? []) as $line) {
            $line = trim($line);
            if (preg_match('/\A([+-]\d+) ([a-z][a-z ]+)\z/i', $line, $parts)) {
                $label = ucwords(strtolower($parts[2]));
                $stats[$label] = ($stats[$label] ?? 0) + (int) $parts[1];
            } elseif (preg_match('/\AEquip: Increases spell power by (\d+)\.?\z/i', $line, $parts)) {
                $stats['Spell Power'] = ($stats['Spell Power'] ?? 0) + (int) $parts[1];
            } else {
                $specialEffect = true;
            }
        }

        return [$stats, $specialEffect];
    }
}
