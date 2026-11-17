<?php

namespace App\Services;

use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WowCharacterSync
{
    public function run(Command $command, PendingRequest $api, string $path, array $query, string $slug): int
    {
        $data = [];
        foreach (['profile' => '', 'statistics' => '/statistics', 'professions' => '/professions', 'equipment' => '/equipment'] as $key => $suffix) {
            $response = $api->get($path.$suffix, $query);
            $command->line(ucfirst($key).": HTTP {$response->status()}");
            if (! $response->successful() || ! is_array($response->json())) {
                $command->error('Sync stopped before saving. The previous snapshot is still available.');

                return Command::FAILURE;
            }
            $data[$key] = $response->json();
        }

        [$first, $last] = explode('-', $slug, 2);
        $profile = $data['profile'];
        $character = $this->label($profile['name'] ?? null) ?? ucfirst($first);
        $surname = $this->label($profile['surname'] ?? null) ?? ucfirst($last);
        $lastSnapshot = DB::table('wow_character_snapshots')
            ->whereRaw('LOWER(realm) = ?', [strtolower($surname)])
            ->whereRaw('LOWER('.DB::connection()->getQueryGrammar()->wrap('character').') = ?', [strtolower($character)])
            ->orderByDesc('id')->first();
        $recipes = $lastSnapshot ? DB::table('wow_learned_recipes')->where('snapshot_id', $lastSnapshot->id)
            ->get()->groupBy('profession')->map(fn ($rows) => $rows->pluck('name')->all())->all() : [];

        [$stats, $recipes] = $this->summarize($data, $recipes);
        $export = tempnam(sys_get_temp_dir(), 'wow-character-');
        if ($export === false) {
            throw new RuntimeException('Could not create a temporary character export.');
        }

        try {
            $payload = json_encode([
                'source' => 'character',
                'captured_at' => now()->utc()->toIso8601String(),
                'realm' => $lastSnapshot->realm ?? $surname,
                'character' => $lastSnapshot->character ?? $character,
                'stats' => $stats,
                'recipes' => $recipes,
            ], JSON_THROW_ON_ERROR);
            if (file_put_contents($export, $payload) === false) {
                throw new RuntimeException('Could not write the temporary character export.');
            }

            return $command->call('wow:import', ['file' => $export]);
        } finally {
            unlink($export);
        }
    }

    public function summarize(array $data, array $recipes = []): array
    {
        $profile = $data['profile'];
        $stats = array_filter([
            'level' => $profile['level'] ?? null,
            'class' => $this->label($profile['character_class'] ?? null),
            'race' => $this->label($profile['race'] ?? null),
            'faction' => $this->label($profile['faction'] ?? null),
            'specialization' => $this->label($profile['active_spec'] ?? null),
        ], 'is_scalar');
        foreach (['health', 'power', 'strength', 'agility', 'intellect', 'stamina', 'armor', 'attack_power',
            'spell_power', 'spell_penetration', 'spell_crit', 'melee_crit', 'melee_haste', 'ranged_crit', 'dodge', 'parry', 'block'] as $key) {
            $number = $data['statistics'][$key] ?? null;
            if (is_array($number)) {
                $number = $number['effective'] ?? $number['value'] ?? $number['rating'] ?? null;
            }
            if (is_numeric($number)) {
                $stats[$key] = $number;
            }
        }

        $professionRanks = [];
        foreach (['primaries', 'secondaries', 'professions'] as $group) {
            foreach (($data['professions'][$group] ?? []) as $entry) {
                $name = $this->label($entry['profession'] ?? $entry['name'] ?? null);
                if ($name === null) {
                    continue;
                }

                $tiers = $entry['tiers'] ?? [];
                $ranks = [];
                foreach ($tiers ?: [$entry] as $tier) {
                    $skill = $tier['skill_points'] ?? null;
                    $max = $tier['max_skill_points'] ?? null;
                    if (is_numeric($skill)) {
                        $ranks[] = $skill.(is_numeric($max) ? " / {$max}" : '');
                    }
                    foreach (($tier['known_recipes'] ?? []) as $recipe) {
                        if ($recipeName = $this->label($recipe)) {
                            $recipes[$name][] = $recipeName;
                        }
                    }
                }
                $professionRanks[$name] = $ranks ? implode(', ', $ranks) : 'Rank unavailable';
            }
        }

        $equipment = [];
        foreach (($data['equipment']['equipped_items'] ?? []) as $item) {
            $slot = $this->label($item['slot'] ?? null);
            $name = $this->label($item['name'] ?? $item['item'] ?? null);
            if ($slot !== null && $name !== null) {
                $lines = [];
                foreach (['stats', 'enchantments', 'spells'] as $group) {
                    foreach (($item[$group] ?? []) as $detail) {
                        $display = $detail['display']['display_string'] ?? $detail['display_string'] ?? $detail['description'] ?? null;
                        if ($line = $this->label($display)) {
                            $lines[] = $line;
                        }
                    }
                }

                $equipment[$slot] = [
                    'name' => $name,
                    'slot_type' => $item['slot']['type'] ?? null,
                    'id' => $item['item']['id'] ?? null,
                    'quality' => WowItemMetadata::quality($item['quality']['type'] ?? null),
                    'level' => $item['level']['value'] ?? null,
                    'armor' => $item['armor']['value'] ?? null,
                    'damage' => $this->label($item['weapon']['damage']['display_string'] ?? null),
                    'speed' => $item['weapon']['attack_speed']['value'] ?? null,
                    'dps' => $item['weapon']['dps']['value'] ?? null,
                    'lines' => array_values(array_unique($lines)),
                ];
            }
        }

        $stats['professions'] = $professionRanks;
        $stats['equipment'] = $equipment;
        return [$stats, $recipes];
    }

    public function talentGroups(array $data): array
    {
        $groups = [];
        foreach (($data['specialization_groups'] ?? []) as $group) {
            $trees = [];
            foreach (($group['specializations'] ?? []) as $tree) {
                $name = $this->label($tree['specialization_name'] ?? $tree['specialization'] ?? null);
                if ($name === null) {
                    continue;
                }

                $talents = [];
                foreach (($tree['talents'] ?? []) as $talent) {
                    if ($label = $this->label($talent['talent'] ?? $talent['spell_tooltip']['spell'] ?? null)) {
                        $talents[] = ['name' => $label, 'rank' => $talent['talent_rank'] ?? null];
                    }
                }
                $trees[] = ['name' => $name, 'points' => (int) ($tree['spent_points'] ?? 0), 'talents' => $talents];
            }
            if ($trees) {
                $groups[] = ['active' => ($group['is_active'] ?? false) === true, 'trees' => $trees];
            }
        }

        return $groups;
    }

    private function label(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = $value['name'] ?? $value['en_US'] ?? $value['type'] ?? null;
            if (is_array($value)) {
                $value = collect($value)->first(fn ($part) => is_string($part));
            }
        }

        return is_string($value) && $value !== '' ? $value : null;
    }
}
