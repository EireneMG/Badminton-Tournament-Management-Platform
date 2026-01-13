<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Club;
use App\Models\ClubPlayer;
use App\Models\EloRating;
use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\TournamentRegistration;
use App\Models\TournamentMatch;
use App\Models\MatchResult;
use App\Models\ManagerIdVerification;
use App\Models\RankingHistory;
use App\Services\EloRatingService;
use App\Enums\TournamentStatus;
use App\Enums\MatchStatus;
use App\Enums\CategoryType;
use App\Enums\SkillLevel;
use App\Helpers\TournamentDayHelper;
use Carbon\Carbon;

/**
 * Production Seeder - Creates realistic tournament data for testing
 * Run: php artisan db:seed --class=ProductionSeeder
 */
class ProductionSeeder extends Seeder
{
    private $password;
    private $now;
    private $matchGenerationService;

    /**
     * Get list of test player emails (only these players will be used by the seeder)
     */
    private function getTestPlayerEmails(): array
    {
        return [
            'player.test1@badmintourph.com', 'player.test2@badmintourph.com', 'player.test3@badmintourph.com',
            'player.test4@badmintourph.com', 'player.test5@badmintourph.com', 'player.test6@badmintourph.com',
            'player.test7@badmintourph.com', 'player.test8@badmintourph.com', 'player.test9@badmintourph.com',
            'player.test10@badmintourph.com', 'player.test11@badmintourph.com', 'player.test12@badmintourph.com',
            'player.test13@badmintourph.com', 'player.test14@badmintourph.com', 'player.test15@badmintourph.com',
            'player.test16@badmintourph.com', 'player.test17@badmintourph.com', 'player.test18@badmintourph.com',
            'player.test19@badmintourph.com', 'player.test20@badmintourph.com', 'player.test21@badmintourph.com',
            'player.test22@badmintourph.com', 'player.test23@badmintourph.com', 'player.test24@badmintourph.com',
            'player.test25@badmintourph.com', 'player.test26@badmintourph.com', 'player.test27@badmintourph.com',
            'player.test28@badmintourph.com', 'player.test29@badmintourph.com', 'player.test30@badmintourph.com',
            'player.test31@badmintourph.com', 'player.test32@badmintourph.com',
        ];
    }

    /**
     * Get list of test manager emails (only these managers will be used by the seeder)
     */
    private function getTestManagerEmails(): array
    {
        return [
            'manager.real1@badmintourph.com',
            'manager.real2@badmintourph.com',
        ];
    }


    public function run(): void
    {
        DB::transaction(function () {
            $this->password = Hash::make('Password123!');
            $this->now = now();

            $this->seedUsers();
            $this->ensureAllManagersVerified();
            $this->seedClubsAndPlayers();
            $this->matchGenerationService = app(\App\Services\MatchGenerationService::class);
            
            // Fix existing round robin tournaments before seeding new ones
            $this->fixAllRoundRobinTournaments();
            
            $this->seedAllTournamentStatuses();
            
            $this->updateMatchesPlayedCount();
        });
    }
    
    /**
     * Fix round names for all existing round robin tournaments
     * This corrects matches that have incorrect round numbers
     */
    private function fixAllRoundRobinTournaments(): void
    {
        $roundRobinTournaments = Tournament::where('bracket_type', 'round_robin')->get();
        
        foreach ($roundRobinTournaments as $tournament) {
            $this->fixRoundRobinRoundNames($tournament);
        }
    }
    
    /**
     * Ensure test managers have verified ID status
     * Only verifies test managers, not real user managers
     */
    private function ensureAllManagersVerified(): void
    {
        // Only verify test managers (managers with test emails)
        $testManagers = User::where('role', 'manager')
            ->whereIn('email', $this->getTestManagerEmails())
            ->get();
            
        foreach ($testManagers as $manager) {
            // Ensure email is verified
            if (!$manager->email_verified_at) {
                $manager->update(['email_verified_at' => $this->now]);
            }
            
            // Ensure biodata is completed
            if (!$manager->biodata_completed) {
                $manager->update(['biodata_completed' => true]);
            }
            
            // Create verified ID verification record for test managers only
            ManagerIdVerification::updateOrCreate(
                ['manager_id' => $manager->id],
                [
                    'id_type' => 'philsys_id', // Use philsys_id (updated enum value)
                    'id_file_path' => 'verified/test-id-' . $manager->id . '.jpg',
                    'status' => 'verified',
                    'submitted_at' => $this->now->subDays(30),
                ]
            );
        }
    }

    /**
     * Seed managers and players with verified accounts
     * 
     * All managers and players are created with:
     * - email_verified_at: Verified email
     * - biodata_completed: true (for players)
     * - ManagerIdVerification: Verified status (for managers, created in ensureAllManagersVerified)
     * 
     * Test users (emails containing .test or manager.test) automatically bypass restrictions
     * via middleware checks in EnsureManagerHasVerifiedId and EnsureManagerHasClub
     */
    private function seedUsers(): void
    {
        // Managers
        $managers = [
            ['email' => 'manager.real1@badmintourph.com', 'first_name' => 'Adrian', 'last_name' => 'Velasquez', 'gender' => 'Male'],
            ['email' => 'manager.real2@badmintourph.com', 'first_name' => 'Bianca', 'last_name' => 'Soriano', 'gender' => 'Female'],
        ];

        foreach ($managers as $mgr) {
            $manager = User::updateOrCreate(
                ['email' => $mgr['email']],
                [
                    'name' => "{$mgr['first_name']} {$mgr['last_name']}",
                    'first_name' => $mgr['first_name'],
                    'last_name' => $mgr['last_name'],
                    'password' => $this->password,
                    'role' => 'manager',
                    'email_verified_at' => $this->now,
                    'verification_status' => 'verified',
                    'biodata_completed' => true,
                    'contact_number' => '+63 917 555 1234',
                    'gender' => $mgr['gender'],
                    'birth_month' => 1,
                    'birth_day' => 1,
                    'birth_year' => 1985,
                    'height' => 175,
                    'weight' => 72,
                    'region' => 'NCR',
                    'province' => 'NCR',
                    'city' => 'Makati City',
                ]
            );
            
            // Create verified ID verification record for manager
            ManagerIdVerification::updateOrCreate(
                ['manager_id' => $manager->id],
                [
                    'id_type' => 'philsys_id', // Use philsys_id (updated enum value)
                    'id_file_path' => 'verified/test-id-' . $manager->id . '.jpg',
                    'status' => 'verified',
                    'submitted_at' => $this->now->subDays(30),
                ]
            );
        }

        // Players - 32 players (16 male, 16 female) with realistic names and age distribution
        // First 8 players (4 male, 4 female) = Junior (ages 15-17)
        // Remaining 24 players (12 male, 12 female) = Senior (ages 18-35)
        $players = [
            // Junior Players (ages 15-17)
            ['email' => 'player.test1@badmintourph.com',  'first_name' => 'Juan',      'last_name' => 'Dela Cruz',    'gender' => 'Male',   'elo' => 2050, 'level' => 'A', 'birth_year' => 2008, 'age_group' => 'Junior'],
            ['email' => 'player.test2@badmintourph.com',  'first_name' => 'Maria',     'last_name' => 'Santos',       'gender' => 'Female', 'elo' => 1840, 'level' => 'B', 'birth_year' => 2009, 'age_group' => 'Junior'],
            ['email' => 'player.test3@badmintourph.com',  'first_name' => 'Carlos',    'last_name' => 'Reyes',        'gender' => 'Male',   'elo' => 1920, 'level' => 'A', 'birth_year' => 2007, 'age_group' => 'Junior'],
            ['email' => 'player.test4@badmintourph.com',  'first_name' => 'Ana',       'last_name' => 'Garcia',       'gender' => 'Female', 'elo' => 1760, 'level' => 'B', 'birth_year' => 2008, 'age_group' => 'Junior'],
            ['email' => 'player.test5@badmintourph.com',  'first_name' => 'Miguel',    'last_name' => 'Lopez',        'gender' => 'Male',   'elo' => 1880, 'level' => 'B', 'birth_year' => 2009, 'age_group' => 'Junior'],
            ['email' => 'player.test6@badmintourph.com',  'first_name' => 'Patricia',  'last_name' => 'Flores',       'gender' => 'Female', 'elo' => 1710, 'level' => 'C', 'birth_year' => 2007, 'age_group' => 'Junior'],
            ['email' => 'player.test7@badmintourph.com',  'first_name' => 'Rafael',    'last_name' => 'Mendoza',      'gender' => 'Male',   'elo' => 1980, 'level' => 'A', 'birth_year' => 2008, 'age_group' => 'Junior'],
            ['email' => 'player.test8@badmintourph.com',  'first_name' => 'Sofia',     'last_name' => 'Valdez',       'gender' => 'Female', 'elo' => 1820, 'level' => 'B', 'birth_year' => 2009, 'age_group' => 'Junior'],
            // Senior Players (ages 18-35)
            ['email' => 'player.test9@badmintourph.com',  'first_name' => 'Diego',     'last_name' => 'Fernandez',    'gender' => 'Male',   'elo' => 1750, 'level' => 'C', 'birth_year' => 1998, 'age_group' => 'Senior'],
            ['email' => 'player.test10@badmintourph.com', 'first_name' => 'Isabella',  'last_name' => 'Torres',       'gender' => 'Female', 'elo' => 1690, 'level' => 'C', 'birth_year' => 1999, 'age_group' => 'Senior'],
            ['email' => 'player.test11@badmintourph.com', 'first_name' => 'Noah',      'last_name' => 'Castillo',     'gender' => 'Male',   'elo' => 1810, 'level' => 'B', 'birth_year' => 1995, 'age_group' => 'Senior'],
            ['email' => 'player.test12@badmintourph.com', 'first_name' => 'Gabriela',  'last_name' => 'Ramos',        'gender' => 'Female', 'elo' => 1650, 'level' => 'C', 'birth_year' => 1997, 'age_group' => 'Senior'],
            ['email' => 'player.test13@badmintourph.com', 'first_name' => 'Liam',      'last_name' => 'Domingo',      'gender' => 'Male',   'elo' => 1720, 'level' => 'C', 'birth_year' => 1996, 'age_group' => 'Senior'],
            ['email' => 'player.test14@badmintourph.com', 'first_name' => 'Chloe',     'last_name' => 'Navarro',      'gender' => 'Female', 'elo' => 1780, 'level' => 'B', 'birth_year' => 1994, 'age_group' => 'Senior'],
            ['email' => 'player.test15@badmintourph.com', 'first_name' => 'Ethan',     'last_name' => 'Vergara',      'gender' => 'Male',   'elo' => 1860, 'level' => 'B', 'birth_year' => 1993, 'age_group' => 'Senior'],
            ['email' => 'player.test16@badmintourph.com', 'first_name' => 'Alexa',     'last_name' => 'Santiago',     'gender' => 'Female', 'elo' => 1900, 'level' => 'A', 'birth_year' => 1992, 'age_group' => 'Senior'],
            ['email' => 'player.test17@badmintourph.com', 'first_name' => 'Marco',     'last_name' => 'Silva',        'gender' => 'Male',   'elo' => 1600, 'level' => 'D', 'birth_year' => 2000, 'age_group' => 'Senior'],
            ['email' => 'player.test18@badmintourph.com', 'first_name' => 'Bianca',    'last_name' => 'Reyes',        'gender' => 'Female', 'elo' => 1580, 'level' => 'D', 'birth_year' => 2001, 'age_group' => 'Senior'],
            ['email' => 'player.test19@badmintourph.com', 'first_name' => 'Paolo',     'last_name' => 'Gutierrez',    'gender' => 'Male',   'elo' => 1650, 'level' => 'C', 'birth_year' => 1997, 'age_group' => 'Senior'],
            ['email' => 'player.test20@badmintourph.com', 'first_name' => 'Clarissa',  'last_name' => 'Lim',          'gender' => 'Female', 'elo' => 1610, 'level' => 'D', 'birth_year' => 1998, 'age_group' => 'Senior'],
            ['email' => 'player.test21@badmintourph.com', 'first_name' => 'Andres',    'last_name' => 'Villanueva',   'gender' => 'Male',   'elo' => 1700, 'level' => 'C', 'birth_year' => 1996, 'age_group' => 'Senior'],
            ['email' => 'player.test22@badmintourph.com', 'first_name' => 'Elena',     'last_name' => 'Rivera',       'gender' => 'Female', 'elo' => 1640, 'level' => 'C', 'birth_year' => 1995, 'age_group' => 'Senior'],
            ['email' => 'player.test23@badmintourph.com', 'first_name' => 'Sergio',    'last_name' => 'Morales',      'gender' => 'Male',   'elo' => 1550, 'level' => 'D', 'birth_year' => 2002, 'age_group' => 'Senior'],
            ['email' => 'player.test24@badmintourph.com', 'first_name' => 'Camille',   'last_name' => 'Tan',          'gender' => 'Female', 'elo' => 1570, 'level' => 'D', 'birth_year' => 2003, 'age_group' => 'Senior'],
            ['email' => 'player.test25@badmintourph.com', 'first_name' => 'Luis',      'last_name' => 'Herrera',      'gender' => 'Male',   'elo' => 1790, 'level' => 'B', 'birth_year' => 1991, 'age_group' => 'Senior'],
            ['email' => 'player.test26@badmintourph.com', 'first_name' => 'Monica',    'last_name' => 'Gonzales',     'gender' => 'Female', 'elo' => 1730, 'level' => 'C', 'birth_year' => 1990, 'age_group' => 'Senior'],
            ['email' => 'player.test27@badmintourph.com', 'first_name' => 'Ricardo',   'last_name' => 'Torres',       'gender' => 'Male',   'elo' => 1820, 'level' => 'B', 'birth_year' => 1989, 'age_group' => 'Senior'],
            ['email' => 'player.test28@badmintourph.com', 'first_name' => 'Nina',      'last_name' => 'Campos',       'gender' => 'Female', 'elo' => 1680, 'level' => 'C', 'birth_year' => 1991, 'age_group' => 'Senior'],
            ['email' => 'player.test29@badmintourph.com', 'first_name' => 'Jorge',     'last_name' => 'Santos',       'gender' => 'Male',   'elo' => 1885, 'level' => 'B', 'birth_year' => 1988, 'age_group' => 'Senior'],
            ['email' => 'player.test30@badmintourph.com', 'first_name' => 'Diana',     'last_name' => 'Lopez',        'gender' => 'Female', 'elo' => 1805, 'level' => 'B', 'birth_year' => 1989, 'age_group' => 'Senior'],
            ['email' => 'player.test31@badmintourph.com', 'first_name' => 'Emilio',    'last_name' => 'Cruz',         'gender' => 'Male',   'elo' => 1960, 'level' => 'A', 'birth_year' => 1987, 'age_group' => 'Senior'],
            ['email' => 'player.test32@badmintourph.com', 'first_name' => 'Valerie',   'last_name' => 'Mendoza',      'gender' => 'Female', 'elo' => 1855, 'level' => 'B', 'birth_year' => 1988, 'age_group' => 'Senior'],
        ];

        foreach ($players as $index => $pl) {
            User::updateOrCreate(
                ['email' => $pl['email']],
                [
                    'name' => "{$pl['first_name']} {$pl['last_name']}",
                    'first_name' => $pl['first_name'],
                    'last_name' => $pl['last_name'],
                    'password' => $this->password,
                    'role' => 'player',
                    'email_verified_at' => $this->now,
                    'verification_status' => 'verified',
                    'biodata_completed' => true,
                    'contact_number' => '+63 900 000 ' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'gender' => $pl['gender'],
                    'birth_month' => 3,
                    'birth_day' => 15,
                    'birth_year' => $pl['birth_year'],
                    'height' => 170,
                    'weight' => 68,
                    'region' => 'NCR',
                    'province' => 'NCR',
                    'city' => 'Quezon City',
                    'school_status' => $pl['age_group'] === 'Junior' ? 'high_school' : 'college_graduate',
                    'school_name' => 'Test University',
                    'years_of_experience' => $pl['age_group'] === 'Junior' ? '1_2' : '3_5',
                    'experience_level' => 'intermediate',
                    'competitive_background' => 'local_tournaments',
                    'badminton_history' => ['tournament', 'community_event'],
                ]
            );
        }
    }

    /**
     * Seed clubs and assign players with ELO ratings
     */
    private function seedClubsAndPlayers(): void
    {
        $manager1 = User::where('email', 'manager.real1@badmintourph.com')->first();
        $manager2 = User::where('email', 'manager.real2@badmintourph.com')->first();

        if (!$manager1 || !$manager2) {
            return;
        }

        // Create clubs
        $club1 = Club::updateOrCreate(
            ['manager_id' => $manager1->id],
            [
                'name' => 'Manila Smash Club',
                'description' => 'Premium indoor badminton facility with coaching programs and weekly ladders. Located in the heart of Makati.',
                'contact_email' => $manager1->email,
                'contact_phone' => '+63 917 555 1234',
                'province' => 'NCR',
                'city' => 'Makati City',
                'active' => true,
            ]
        );

        $club2 = Club::updateOrCreate(
            ['manager_id' => $manager2->id],
            [
                'name' => 'Quezon Power Badminton Center',
                'description' => 'Community-driven club hosting junior and senior circuits, with certified coaches and modern facilities.',
                'contact_email' => $manager2->email,
                'contact_phone' => '+63 917 555 5678',
                'province' => 'NCR',
                'city' => 'Quezon City',
                'active' => true,
            ]
        );

        // Get only test players (created by seeder) and assign to clubs with ELO
        // Only assign players with test emails to avoid affecting real user accounts
        $players = User::where('role', 'player')
            ->whereIn('email', $this->getTestPlayerEmails())
            ->orderBy('id')
            ->get();
        $playerData = [
            ['elo' => 2050, 'level' => 'A'], ['elo' => 1840, 'level' => 'B'], ['elo' => 1920, 'level' => 'A'],
            ['elo' => 1760, 'level' => 'B'], ['elo' => 1880, 'level' => 'B'], ['elo' => 1710, 'level' => 'C'],
            ['elo' => 1980, 'level' => 'A'], ['elo' => 1820, 'level' => 'B'], ['elo' => 1750, 'level' => 'C'],
            ['elo' => 1690, 'level' => 'C'], ['elo' => 1810, 'level' => 'B'], ['elo' => 1650, 'level' => 'C'],
            ['elo' => 1720, 'level' => 'C'], ['elo' => 1780, 'level' => 'B'], ['elo' => 1860, 'level' => 'B'],
            ['elo' => 1900, 'level' => 'A'], ['elo' => 1600, 'level' => 'D'], ['elo' => 1580, 'level' => 'D'],
            ['elo' => 1650, 'level' => 'C'], ['elo' => 1610, 'level' => 'D'], ['elo' => 1700, 'level' => 'C'],
            ['elo' => 1640, 'level' => 'C'], ['elo' => 1550, 'level' => 'D'], ['elo' => 1570, 'level' => 'D'],
            ['elo' => 1790, 'level' => 'B'], ['elo' => 1730, 'level' => 'C'], ['elo' => 1820, 'level' => 'B'],
            ['elo' => 1680, 'level' => 'C'], ['elo' => 1885, 'level' => 'B'], ['elo' => 1805, 'level' => 'B'],
            ['elo' => 1960, 'level' => 'A'], ['elo' => 1855, 'level' => 'B'],
        ];

        foreach ($players as $index => $player) {
            $data = $playerData[$index] ?? ['elo' => 1500, 'level' => 'C'];
            $club = $index < 16 ? $club1 : $club2;

            // Assign to club
            ClubPlayer::updateOrCreate(
                ['club_id' => $club->id, 'player_id' => $player->id],
                [
                    'status' => 'approved',
                    'skill_level' => match($data['level']) {
                        'A' => SkillLevel::A->value,
                        'B' => SkillLevel::B->value,
                        'C' => SkillLevel::C->value,
                        'D' => SkillLevel::D->value,
                        default => SkillLevel::C->value,
                    },
                    'provisional_elo' => $data['elo'],
                    'is_provisional' => false,
                ]
            );

            // Create ELO ratings for gender-appropriate categories ONLY
            // Male players: MS, MD, XD
            // Female players: WS, WD, XD
            // This ensures proper category filtering in rankings and player profiles
            $isMale = ucfirst(strtolower($player->gender ?? '')) === 'Male';
            $categories = $isMale 
                ? [CategoryType::MENS_SINGLES->value, CategoryType::MENS_DOUBLES->value, CategoryType::MIXED_DOUBLES->value]
                : [CategoryType::WOMENS_SINGLES->value, CategoryType::WOMENS_DOUBLES->value, CategoryType::MIXED_DOUBLES->value];
            
            foreach ($categories as $cat) {
                EloRating::updateOrCreate(
                    ['player_id' => $player->id, 'category' => $cat],
                    [
                        'current_rating' => $data['elo'],
                        'peak_rating' => $data['elo'] + 50,
                        'matches_played' => 0, // Start at 0 - will be updated by updateMatchesPlayedCount() after tournaments are seeded
                    ]
                );
                
                // Create ranking history entry for initial ELO rating (matches actual application flow)
                RankingHistory::updateOrCreate(
                    [
                        'player_id' => $player->id,
                        'category' => $cat,
                        'recorded_at' => $this->now->subDays(60), // Initial rating from 60 days ago
                    ],
                    [
                        'rating' => $data['elo'],
                        'previous_rating' => null,
                        'change' => 0,
                        'rank' => null,
                        'tournament_id' => null,
                    ]
                );
            }
        }
    }

    private function seedAllTournamentStatuses(): void
    {
        $managers = [
            User::where('email', 'manager.real1@badmintourph.com')->first(),
            User::where('email', 'manager.real2@badmintourph.com')->first(),
        ];

        $players = User::where('role', 'player')
            ->whereIn('email', $this->getTestPlayerEmails())
            ->get();
        if ($players->count() < 32) return;

        foreach ($managers as $managerIndex => $manager) {
            if (!$manager) continue;

            $club = Club::where('manager_id', $manager->id)->first();
            if (!$club) continue;

            $clubPrefix = $club->name === 'Manila Smash Club' ? 'Manila' : 'Quezon';
            $venue = $club->name === 'Manila Smash Club' ? 'Manila Badminton Center' : 'Quezon Sports Complex';

            $formats = [
                ['type' => 'single_elimination', 'label' => 'Single Elimination'],
                ['type' => 'round_robin', 'label' => 'Round Robin'],
            ];

            $statuses = [
                ['status' => TournamentStatus::PUBLISHED->value, 'start_offset' => 7, 'end_offset' => 10],
                ['status' => TournamentStatus::UPCOMING->value, 'start_offset' => 30, 'end_offset' => 33],
                ['status' => TournamentStatus::ONGOING->value, 'start_offset' => -2, 'end_offset' => 1],
                ['status' => TournamentStatus::COMPLETED->value, 'start_offset' => -30, 'end_offset' => -27],
            ];

            foreach ($formats as $format) {
                foreach ($statuses as $statusData) {
                    $name = "{$clubPrefix} {$format['label']} {$statusData['status']}";
                    $this->resetTournamentData($name);

                    $startDate = now()->addDays($statusData['start_offset']);
                    $endDate = now()->addDays($statusData['end_offset']);

                    $tournament = Tournament::updateOrCreate(
                        ['name' => $name],
                        [
                            'description' => "{$format['label']} format tournament ({$statusData['status']} status). All categories available.",
                            'organizer_id' => $manager->id,
                            'club_id' => $club->id,
                            'type' => 'singles',
                            'venue_name' => $venue,
                            'number_of_courts' => 4,
                            'contact_email' => $manager->email,
                            'contact_phone' => '+63 917 555 ' . ($club->name === 'Manila Smash Club' ? '1234' : '5678'),
                            'start_date' => $startDate->toDateString(),
                            'end_date' => $endDate->toDateString(),
                            'registration_deadline' => $startDate->copy()->subDays(4)->toDateString(),
                            'withdrawal_deadline' => $startDate->copy()->subDays(2),
                            'location' => $club->city . ', NCR',
                            'registration_fee' => 500,
                            'tournament_fee' => 500,
                            'status' => $statusData['status'],
                            'bracket_type' => $format['type'],
                            'archived' => false,
                        ]
                    );

                    $categories = ["Men's Singles", "Women's Singles", "Men's Doubles", "Women's Doubles", "Mixed Doubles"];
                    $catMap = [];

                    foreach ($categories as $catName) {
                        $isSingles = in_array($catName, ["Men's Singles", "Women's Singles"]);
                        $maxParticipants = $isSingles ? 16 : 32;

                        $catMap[$catName] = TournamentCategory::updateOrCreate(
                            ['tournament_id' => $tournament->id, 'name' => $catName],
                            [
                                'max_participants' => $maxParticipants,
                                'match_duration_minutes' => $isSingles ? 45 : 60,
                                'break_between_matches_minutes' => 5,
                                'skill_level' => SkillLevel::OPEN->value,
                                'schedule_start_time' => '09:00:00',
                                'schedule_start_date' => $tournament->start_date,
                            ]
                        );
                    }

                    $this->registerPlayersForTournament($tournament, $catMap, $players, $club);

                    if ($statusData['status'] === TournamentStatus::COMPLETED->value) {
                        $males = $players->filter(fn($p) => ucfirst(strtolower($p->gender ?? '')) === 'Male')
                            ->map(function($p) {
                                $elo = EloRating::where('player_id', $p->id)->where('category', CategoryType::MENS_SINGLES->value)->first();
                                $p->elo_rating = $elo?->current_rating ?? 1500;
                                return $p;
                            })
                            ->sortByDesc('elo_rating')
                            ->values();

                        $females = $players->filter(fn($p) => ucfirst(strtolower($p->gender ?? '')) === 'Female')
                            ->map(function($p) {
                                $elo = EloRating::where('player_id', $p->id)->where('category', CategoryType::WOMENS_SINGLES->value)->first();
                                $p->elo_rating = $elo?->current_rating ?? 1500;
                                return $p;
                            })
                            ->sortByDesc('elo_rating')
                            ->values();

                        if ($format['type'] === 'single_elimination') {
                            $this->createCompletedBrackets($tournament, $catMap, $males, $females, $manager->id, $startDate);
                        } else {
                            $this->createMatchesForTournament($tournament, $catMap, $format['type']);
                            // Fix round names before creating results
                            $this->fixRoundRobinRoundNames($tournament);
                            $this->createCompletedRoundRobinResults($tournament, $catMap, $males, $females, $manager->id, $startDate);
                        }
                    } elseif (in_array($statusData['status'], [TournamentStatus::UPCOMING->value, TournamentStatus::ONGOING->value])) {
                        $this->createMatchesForTournament($tournament, $catMap, $format['type']);
                        // Fix round names for existing tournaments
                        if ($format['type'] === 'round_robin') {
                            $this->fixRoundRobinRoundNames($tournament);
                        }
                    }
                }
            }
        }
    }

    private function registerPlayersForTournament($tournament, $catMap, $players, $club): void
    {
        $males = $players->filter(fn($p) => ucfirst(strtolower($p->gender ?? '')) === 'Male')->values();
        $females = $players->filter(fn($p) => ucfirst(strtolower($p->gender ?? '')) === 'Female')->values();

        if (isset($catMap["Men's Singles"])) {
            $maxParticipants = $catMap["Men's Singles"]->max_participants;
            foreach ($males->take($maxParticipants) as $p) {
                TournamentRegistration::updateOrCreate(
                    ['tournament_id' => $tournament->id, 'category_id' => $catMap["Men's Singles"]->id, 'player_id' => $p->id],
                    ['status' => 'approved']
                );
            }
        }

        if (isset($catMap["Women's Singles"])) {
            $maxParticipants = $catMap["Women's Singles"]->max_participants;
            foreach ($females->take($maxParticipants) as $p) {
                TournamentRegistration::updateOrCreate(
                    ['tournament_id' => $tournament->id, 'category_id' => $catMap["Women's Singles"]->id, 'player_id' => $p->id],
                    ['status' => 'approved']
                );
            }
        }

        if (isset($catMap["Men's Doubles"])) {
            $maxParticipants = $catMap["Men's Doubles"]->max_participants;
            $teamCount = floor($maxParticipants / 2);
            $mdMales = $males->take($teamCount * 2)->values();
            for ($i = 0; $i < $teamCount; $i++) {
                $p1 = $mdMales[$i * 2] ?? null;
                $p2 = $mdMales[$i * 2 + 1] ?? null;
                if ($p1 && $p2) {
                    TournamentRegistration::updateOrCreate(
                        ['tournament_id' => $tournament->id, 'category_id' => $catMap["Men's Doubles"]->id, 'player_id' => $p1->id],
                        ['partner_id' => $p2->id, 'status' => 'approved']
                    );
                }
            }
        }

        if (isset($catMap["Women's Doubles"])) {
            $maxParticipants = $catMap["Women's Doubles"]->max_participants;
            $teamCount = floor($maxParticipants / 2);
            $wdFemales = $females->take($teamCount * 2)->values();
            for ($i = 0; $i < $teamCount; $i++) {
                $p1 = $wdFemales[$i * 2] ?? null;
                $p2 = $wdFemales[$i * 2 + 1] ?? null;
                if ($p1 && $p2) {
                    TournamentRegistration::updateOrCreate(
                        ['tournament_id' => $tournament->id, 'category_id' => $catMap["Women's Doubles"]->id, 'player_id' => $p1->id],
                        ['partner_id' => $p2->id, 'status' => 'approved']
                    );
                }
            }
        }

        if (isset($catMap["Mixed Doubles"])) {
            $maxParticipants = $catMap["Mixed Doubles"]->max_participants;
            $teamCount = floor($maxParticipants / 2);
            $xdMales = $males->take($teamCount)->values();
            $xdFemales = $females->take($teamCount)->values();
            for ($i = 0; $i < $teamCount; $i++) {
                $male = $xdMales[$i] ?? null;
                $female = $xdFemales[$i] ?? null;
                if ($male && $female) {
                    TournamentRegistration::updateOrCreate(
                        ['tournament_id' => $tournament->id, 'category_id' => $catMap["Mixed Doubles"]->id, 'player_id' => $male->id],
                        ['partner_id' => $female->id, 'status' => 'approved']
                    );
                }
            }
        }
    }

    private function createMatchesForTournament($tournament, $catMap, $bracketType): void
    {
        try {
            // Ensure tournament bracket_type matches the requested bracket type
            if ($tournament->bracket_type !== $bracketType) {
                $tournament->update(['bracket_type' => $bracketType]);
                $tournament->refresh();
            }
            
            // For round robin tournaments, ensure all existing matches are deleted
            // This prevents any leftover matches with incorrect round names
            if ($bracketType === 'round_robin') {
                $matchIds = TournamentMatch::where('tournament_id', $tournament->id)->pluck('id');
                if ($matchIds->isNotEmpty()) {
                    MatchResult::whereIn('match_id', $matchIds)->delete();
                    TournamentMatch::where('tournament_id', $tournament->id)->delete();
                }
            }
            
            $this->matchGenerationService->generateMatches($tournament, $bracketType);
            
            // Fix round names for round robin tournaments (in case of existing incorrect data)
            if ($bracketType === 'round_robin') {
                $this->fixRoundRobinRoundNames($tournament);
                
                // Verify round robin matches start from Round 1
                $matches = TournamentMatch::where('tournament_id', $tournament->id)->get();
                $roundNames = $matches->pluck('round')->unique()->sort()->values();
                
                // Check if first round is Round 1
                $firstRound = $roundNames->first();
                if ($firstRound && !preg_match('/^Round\s+1$/i', $firstRound)) {
                    \Log::warning("Round robin tournament {$tournament->name} does not start with Round 1. First round: {$firstRound}. Attempting to fix...");
                    // Try to fix by regenerating matches
                    $matchIds = TournamentMatch::where('tournament_id', $tournament->id)->pluck('id');
                    if ($matchIds->isNotEmpty()) {
                        MatchResult::whereIn('match_id', $matchIds)->delete();
                        TournamentMatch::where('tournament_id', $tournament->id)->delete();
                        $this->matchGenerationService->generateMatches($tournament, $bracketType);
                    }
                }
                
                // Check for any elimination rounds that shouldn't be there
                $invalidRounds = $roundNames->filter(function($round) {
                    $r = strtolower(trim($round));
                    return preg_match('/round\s+of\s+\d+/i', $r) || 
                           str_contains($r, 'quarter') || 
                           str_contains($r, 'semi') || 
                           (str_contains($r, 'final') && !str_contains($r, 'semi'));
                });
                
                if ($invalidRounds->isNotEmpty()) {
                    \Log::warning("Round robin tournament {$tournament->name} contains invalid elimination rounds: " . $invalidRounds->implode(', '));
                    // Delete matches with invalid round names
                    TournamentMatch::where('tournament_id', $tournament->id)
                        ->whereIn('round', $invalidRounds->toArray())
                        ->delete();
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to generate matches for {$tournament->name}: " . $e->getMessage());
        }
    }

    private function seedUpcomingTournaments(): void
    {
        $managers = [
            User::where('email', 'manager.real1@badmintourph.com')->first(),
            User::where('email', 'manager.real2@badmintourph.com')->first(),
        ];

        foreach ($managers as $manager) {
            if (!$manager) continue;

            $club = Club::where('manager_id', $manager->id)->first();
            if (!$club) continue;

            $tournaments = [
                [
                    'name' => ($club->name === 'Manila Smash Club' ? 'Manila' : 'Quezon') . ' Junior Championship 2025',
                    'description' => 'Junior division (Under 18) tournament. Open to all skill levels. Showcasing the next generation of badminton talent.',
                    'start_date' => now()->addDays(30),
                    'end_date' => now()->addDays(32),
                    'courts' => 4,
                    'is_dual_meet' => false,
                    'min_age' => null,  // Junior: Under 18
                    'max_age' => 17,    // Junior: max_age = 17 means Under 18
                    'categories' => ["Men's Singles", "Women's Singles", "Men's Doubles", "Women's Doubles", "Mixed Doubles"],
                ],
                [
                    'name' => ($club->name === 'Manila Smash Club' ? 'Manila' : 'Quezon') . ' Senior Championship 2025',
                    'description' => 'Senior division (18+) tournament with all categories. Open to all skill levels. Competitive play for experienced players.',
                    'start_date' => now()->addDays(40),
                    'end_date' => now()->addDays(43),
                    'courts' => 5,
                    'is_dual_meet' => false,
                    'min_age' => 18,    // Senior: 18+
                    'max_age' => null,  // Senior: no upper limit
                    'categories' => ["Men's Singles", "Women's Singles", "Men's Doubles", "Women's Doubles", "Mixed Doubles"],
                ],
            ];

            foreach ($tournaments as $t) {
                $this->resetTournamentData($t['name']);

                $tournament = Tournament::updateOrCreate(
                    ['name' => $t['name']],
                    [
                        'description' => $t['description'],
                        'organizer_id' => $manager->id,
                        'club_id' => $club->id,
                        'type' => 'singles',
                        'venue_name' => $club->name === 'Manila Smash Club' ? 'Ayala Badminton Center' : 'Quezon Sports Complex',
                        'number_of_courts' => $t['courts'],
                        'contact_email' => $manager->email,
                        'contact_phone' => '+63 917 555 ' . ($club->name === 'Manila Smash Club' ? '1234' : '5678'),
                        'start_date' => $t['start_date']->toDateString(),
                        'end_date' => $t['end_date']->toDateString(),
                        'registration_deadline' => $t['start_date']->copy()->subDays(7)->toDateString(),
                        'withdrawal_deadline' => $t['start_date']->copy()->subDays(5),
                        'location' => $club->city . ', NCR',
                        'registration_fee' => 500,
                        'status' => TournamentStatus::UPCOMING->value,
                        'is_dual_meet' => $t['is_dual_meet'],
                        'bracket_type' => 'single_elimination',
                        'archived' => false,
                    ]
                );

                foreach ($t['categories'] as $catName) {
                    TournamentCategory::updateOrCreate(
                        ['tournament_id' => $tournament->id, 'name' => $catName],
                        [
                            'max_participants' => 16,
                            'match_duration_minutes' => 21,
                            'break_between_matches_minutes' => 5,
                            'skill_level' => SkillLevel::OPEN->value,
                            'min_age' => $t['min_age'],  // Set age division
                            'max_age' => $t['max_age'],  // Set age division
                        ]
                    );
                }
            }
        }
    }

    /**
     * Seed 2 ongoing tournaments per club (4 total)
     * - Ongoing Tournament #1: Open to all ages, Open to all skill levels, All categories, Round of 32, December 9–11
     * - Ongoing Tournament #2: Open to all ages, Open to all skill levels, All categories, Round of 16, December 10–13
     */
    private function seedOngoingTournaments(): void
    {
        $managers = [
            User::where('email', 'manager.real1@badmintourph.com')->first(),
            User::where('email', 'manager.real2@badmintourph.com')->first(),
        ];
        
        // Only use test players for seeding tournaments
        $players = User::where('role', 'player')
            ->whereIn('email', $this->getTestPlayerEmails())
            ->get();
        if ($players->count() < 32) {
            return;
        }

        foreach ($managers as $managerIndex => $manager) {
            if (!$manager) continue;

            $club = Club::where('manager_id', $manager->id)->first();
            if (!$club) continue;

            $tournaments = [
                [
                    'name' => ($club->name === 'Manila Smash Club' ? 'Manila' : 'Quezon') . ' Open Championship 2025',
                    'description' => 'Open to all ages and skill levels. All categories tournament. Singles: 16 slots, Doubles: 32 slots.',
                    'start_date' => Carbon::parse('2025-12-09'),
                    'end_date' => Carbon::parse('2025-12-11'),
                    'categories' => ["Men's Singles", "Women's Singles", "Men's Doubles", "Women's Doubles", "Mixed Doubles"],
                ],
                [
                    'name' => ($club->name === 'Manila Smash Club' ? 'Manila' : 'Quezon') . ' Winter Classic 2025',
                    'description' => 'Open to all ages and skill levels. All categories tournament. Singles: 16 slots, Doubles: 32 slots.',
                    'start_date' => Carbon::parse('2025-12-10'),
                    'end_date' => Carbon::parse('2025-12-13'),
                    'categories' => ["Men's Singles", "Women's Singles", "Men's Doubles", "Women's Doubles", "Mixed Doubles"],
                ],
            ];

            foreach ($tournaments as $t) {
                $this->resetTournamentData($t['name']);

                $tournament = Tournament::updateOrCreate(
                    ['name' => $t['name']],
                    [
                        'description' => $t['description'],
                        'organizer_id' => $manager->id,
                        'club_id' => $club->id,
                        'type' => 'singles',
                        'venue_name' => $club->name === 'Manila Smash Club' ? 'Ultra Badminton Arena' : 'Quezon Sports Complex',
                        'number_of_courts' => 6,
                        'contact_email' => $manager->email,
                        'contact_phone' => '+63 917 555 ' . ($club->name === 'Manila Smash Club' ? '1234' : '5678'),
                        'start_date' => $t['start_date']->toDateString(),
                        'end_date' => $t['end_date']->toDateString(),
                        'registration_deadline' => $t['start_date']->copy()->subDays(5)->toDateString(),
                        'withdrawal_deadline' => $t['start_date']->copy()->subDays(3),
                        'location' => $club->city . ', NCR',
                        'registration_fee' => 550,
                        'status' => TournamentStatus::ONGOING->value,
                        'bracket_type' => 'single_elimination',
                        'archived' => false,
                    ]
                );

                // Create categories with appropriate slots:
                // Singles: 16 slots (8 males + 8 females per club = 16 total)
                // Doubles: 32 slots (16 teams = 32 players, need both clubs)
                $catMap = [];
                foreach ($t['categories'] as $catName) {
                    // Determine max_participants based on category type
                    $isSingles = in_array($catName, ["Men's Singles", "Women's Singles"]);
                    $maxParticipants = $isSingles ? 16 : 32;
                    
                    $catMap[$catName] = TournamentCategory::updateOrCreate(
                        ['tournament_id' => $tournament->id, 'name' => $catName],
                        [
                            'max_participants' => $maxParticipants,
                            'match_duration_minutes' => 21,
                            'break_between_matches_minutes' => 5,
                            'skill_level' => SkillLevel::OPEN->value,
                        ]
                    );
                }

                // Get players sorted by ELO (use club players if available)
                // For doubles categories (32 slots), we need players from both clubs
                // For singles categories (16 slots), club players are sufficient
                $clubPlayerIds = ClubPlayer::where('club_id', $club->id)
                    ->where('status', 'approved')
                    ->pluck('player_id')
                    ->toArray();
                
                $clubPlayers = $players->filter(fn($p) => in_array($p->id, $clubPlayerIds));
                
                // Use all players if club has less than 16, or if tournament has doubles categories (need 32 players)
                $hasDoubles = !empty(array_intersect($t['categories'], ["Men's Doubles", "Women's Doubles", "Mixed Doubles"]));
                if ($clubPlayers->count() < 16 || $hasDoubles) {
                    $clubPlayers = $players; // Use all players for doubles categories or if club is small
                }

                $males = $clubPlayers->filter(fn($p) => ucfirst(strtolower($p->gender ?? '')) === 'Male')
                    ->map(function($p) {
                        $elo = EloRating::where('player_id', $p->id)->where('category', CategoryType::MENS_SINGLES->value)->first();
                        $p->elo_rating = $elo?->current_rating ?? 1500;
                        return $p;
                    })
                    ->sortByDesc('elo_rating')
                    ->values();

                $females = $clubPlayers->filter(fn($p) => ucfirst(strtolower($p->gender ?? '')) === 'Female')
                    ->map(function($p) {
                        $elo = EloRating::where('player_id', $p->id)->where('category', CategoryType::WOMENS_SINGLES->value)->first();
                        $p->elo_rating = $elo?->current_rating ?? 1500;
                        return $p;
                    })
                    ->sortByDesc('elo_rating')
                    ->values();

                // Register players and create matches
                // Note: max_participants is now per-category, so we pass the category map
                $this->registerAndCreateMatches($tournament, $catMap, $males, $females, $manager->id, $t['start_date']);
            }
        }
    }

    /**
     * Register players and create matches for ongoing tournaments
     */
    private function registerAndCreateMatches($tournament, $catMap, $males, $females, $managerId, $startDate): void
    {
        // Men's Singles
        if (isset($catMap["Men's Singles"])) {
            $maxParticipants = $catMap["Men's Singles"]->max_participants;
            $participantCount = min($maxParticipants, $males->count());
            // Use TournamentRoundHelper for consistent round naming
            $numRounds = ceil(log($participantCount, 2));
            $roundName = \App\Helpers\TournamentRoundHelper::getRoundName('single_elimination', 1, $numRounds);
            $msPlayers = $males->take($participantCount)->values();
            foreach ($msPlayers as $p) {
                TournamentRegistration::updateOrCreate(
                    ['tournament_id' => $tournament->id, 'category_id' => $catMap["Men's Singles"]->id, 'player_id' => $p->id],
                    ['status' => 'approved']
                );
            }
            // Create matches based on participant count
            $matchCount = floor($participantCount / 2);
            for ($i = 0; $i < $matchCount; $i++) {
                $p1 = $msPlayers[$i] ?? null;
                $p2 = $msPlayers[$participantCount - 1 - $i] ?? null;
                if (!$p1 || !$p2) continue;

                // For ongoing tournaments, create matches with varied statuses:
                // - First 2 matches: completed (already have results) - scheduled for Dec 9
                // - Next 2-3 matches: scheduled for Dec 9 at times that have passed (can input results)
                // - Remaining matches: scheduled for future dates
                $completed = $i < 2;
                $canInputResult = $i >= 2 && $i < 5; // Matches 3-5 can have results input
                
                // Calculate match date and time
                // For ongoing tournaments, schedule matches starting from Dec 9 (yesterday)
                $yesterday = Carbon::parse('2025-12-09');
                $matchDate = $startDate->copy();
                $matchTime = $startDate->copy()->setTime(10 + $i, 0);
                
                if ($completed || $canInputResult) {
                    // Schedule completed and input-result matches for Dec 9 (yesterday)
                    // This allows managers to test inputting results and see bracket updates
                    $matchDate = $yesterday;
                    if ($completed) {
                        // Completed matches: scheduled earlier in the day (9 AM, 10 AM)
                        $matchTime = $yesterday->copy()->setTime(9 + $i, 0);
                    } else {
                        // Matches that can have results input: scheduled later in the day (2 PM, 3 PM, 4 PM)
                        $matchTime = $yesterday->copy()->setTime(14 + ($i - 2), 0);
                    }
                }
                
                $match = TournamentMatch::updateOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'tournament_category_id' => $catMap["Men's Singles"]->id,
                        'round' => $roundName,
                        'match_number' => $i + 1,
                    ],
                    [
                        'player1_id' => $p1->id,
                        'player2_id' => $p2->id,
                        'scheduled_date' => $matchDate->toDateString(),
                        'scheduled_time' => $matchTime,
                        'court_number' => ($i % 6) + 1,
                        'status' => $completed ? MatchStatus::COMPLETED->value : MatchStatus::SCHEDULED->value,
                        'tournament_day' => \App\Helpers\TournamentDayHelper::calculateTournamentDay(1, 'single_elimination', $numRounds),
                        'winner_id' => $completed ? $p1->id : null,
                    ]
                );

                if ($completed) {
                    // Create match result
                    $result = MatchResult::updateOrCreate(
                        ['match_id' => $match->id],
                        [
                            'winner_id' => $p1->id,
                            'player1_set1_score' => 21,
                            'player2_set1_score' => 17,
                            'player1_set2_score' => 21,
                            'player2_set2_score' => 16,
                            'score_inputted_by' => 'manager',
                            'inputted_by_user_id' => $managerId,
                            'elo_updated' => false, // Will be set to true after ELO update
                        ]
                    );
                    
                    // Update ELO ratings using EloRatingService (matches actual application flow)
                    // This automatically creates RankingHistory records
                    $eloService = app(EloRatingService::class);
                    $eloService->calculateMatchRatings($p1, $p2, true, CategoryType::MENS_SINGLES->value, $tournament->id);
                    
                    // Mark ELO as updated to prevent double updates
                    $result->update(['elo_updated' => true]);
                }
            }
        }

        // Women's Singles
        if (isset($catMap["Women's Singles"])) {
            $maxParticipants = $catMap["Women's Singles"]->max_participants;
            $participantCount = min($maxParticipants, $females->count());
            // Use TournamentRoundHelper for consistent round naming
            $numRounds = ceil(log($participantCount, 2));
            $roundName = \App\Helpers\TournamentRoundHelper::getRoundName('single_elimination', 1, $numRounds);
            $wsPlayers = $females->take($participantCount)->values();
            foreach ($wsPlayers as $p) {
                TournamentRegistration::updateOrCreate(
                    ['tournament_id' => $tournament->id, 'category_id' => $catMap["Women's Singles"]->id, 'player_id' => $p->id],
                    ['status' => 'approved']
                );
            }
            $matchCount = floor($participantCount / 2);
            for ($i = 0; $i < $matchCount; $i++) {
                $p1 = $wsPlayers[$i] ?? null;
                $p2 = $wsPlayers[$participantCount - 1 - $i] ?? null;
                if (!$p1 || !$p2) continue;

                // For ongoing tournaments, create matches with varied statuses
                $completed = $i < 2;
                $canInputResult = $i >= 2 && $i < 5; // Matches 3-5 can have results input
                
                // Calculate match date and time
                $yesterday = Carbon::parse('2025-12-09');
                $matchDate = $startDate->copy();
                $matchTime = $startDate->copy()->setTime(14 + $i, 0);
                
                if ($completed || $canInputResult) {
                    // Schedule completed and input-result matches for Dec 9 (yesterday)
                    $matchDate = $yesterday;
                    if ($completed) {
                        // Completed matches: scheduled earlier (2 PM, 3 PM)
                        $matchTime = $yesterday->copy()->setTime(14 + $i, 0);
                    } else {
                        // Matches that can have results input: scheduled later (4 PM, 5 PM, 6 PM)
                        $matchTime = $yesterday->copy()->setTime(16 + ($i - 2), 0);
                    }
                }
                
                $match = TournamentMatch::updateOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'tournament_category_id' => $catMap["Women's Singles"]->id,
                        'round' => $roundName,
                        'match_number' => $i + 1,
                    ],
                    [
                        'player1_id' => $p1->id,
                        'player2_id' => $p2->id,
                        'scheduled_date' => $matchDate->toDateString(),
                        'scheduled_time' => $matchTime,
                        'court_number' => ($i % 6) + 1,
                        'status' => $completed ? MatchStatus::COMPLETED->value : MatchStatus::SCHEDULED->value,
                        'tournament_day' => \App\Helpers\TournamentDayHelper::calculateTournamentDay(1, 'single_elimination', $numRounds),
                        'winner_id' => $completed ? $p1->id : null,
                    ]
                );

                if ($completed) {
                    // Create match result
                    $result = MatchResult::updateOrCreate(
                        ['match_id' => $match->id],
                        [
                            'winner_id' => $p1->id,
                            'player1_set1_score' => 21,
                            'player2_set1_score' => 18,
                            'player1_set2_score' => 21,
                            'player2_set2_score' => 15,
                            'score_inputted_by' => 'manager',
                            'inputted_by_user_id' => $managerId,
                            'elo_updated' => false,
                        ]
                    );
                    
                    // Update ELO ratings using EloRatingService (matches actual application flow)
                    $eloService = app(EloRatingService::class);
                    $eloService->calculateMatchRatings($p1, $p2, true, CategoryType::WOMENS_SINGLES->value, $tournament->id);
                    
                    // Mark ELO as updated
                    $result->update(['elo_updated' => true]);
                }
            }
        }

        // Men's Doubles
        if (isset($catMap["Men's Doubles"])) {
            $maxParticipants = $catMap["Men's Doubles"]->max_participants;
            $participantCount = min($maxParticipants, $males->count());
            $teamCount = floor($participantCount / 2);
            $mdPlayers = $males->take($teamCount * 2)->values();
            $teams = [];
            for ($i = 0; $i < $teamCount * 2; $i += 2) {
                if (isset($mdPlayers[$i]) && isset($mdPlayers[$i + 1])) {
                    $teams[] = [$mdPlayers[$i], $mdPlayers[$i + 1]];
                }
            }
            $teams = collect($teams)->take($teamCount)->values();
            // Use TournamentRoundHelper for consistent round naming
            $numRounds = ceil(log($teamCount, 2));
            $doublesRoundName = \App\Helpers\TournamentRoundHelper::getRoundName('single_elimination', 1, $numRounds);

            foreach ($teams as $team) {
                TournamentRegistration::updateOrCreate(
                    ['tournament_id' => $tournament->id, 'category_id' => $catMap["Men's Doubles"]->id, 'player_id' => $team[0]->id],
                    ['partner_id' => $team[1]->id, 'status' => 'approved']
                );
            }

            // Create matches
            $matchCount = floor($teamCount / 2);
            for ($i = 0; $i < $matchCount; $i++) {
                if (!isset($teams[$i * 2]) || !isset($teams[$i * 2 + 1])) continue;
                $teamA = $teams[$i * 2];
                $teamB = $teams[$i * 2 + 1];

                // For ongoing tournaments, create matches with varied statuses
                $completed = $i < 1;
                $canInputResult = $i >= 1 && $i < 3; // Matches 2-3 can have results input
                
                // Calculate match date and time
                $yesterday = Carbon::parse('2025-12-09');
                $matchDate = $startDate->copy()->addDay();
                $matchTime = $startDate->copy()->addDay()->setTime(10 + $i, 0);
                
                if ($completed || $canInputResult) {
                    // Schedule completed and input-result matches for Dec 9 (yesterday)
                    $matchDate = $yesterday;
                    if ($completed) {
                        // Completed match: scheduled earlier (10 AM)
                        $matchTime = $yesterday->copy()->setTime(10 + $i, 0);
                    } else {
                        // Matches that can have results input: scheduled later (11 AM, 12 PM)
                        $matchTime = $yesterday->copy()->setTime(11 + ($i - 1), 0);
                    }
                }
                
                $match = TournamentMatch::updateOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'tournament_category_id' => $catMap["Men's Doubles"]->id,
                        'round' => $doublesRoundName,
                        'match_number' => $i + 1,
                    ],
                    [
                        'player1_id' => $teamA[0]->id,
                        'player1_partner_id' => $teamA[1]->id,
                        'player2_id' => $teamB[0]->id,
                        'player2_partner_id' => $teamB[1]->id,
                        'scheduled_date' => $matchDate->toDateString(),
                        'scheduled_time' => $matchTime,
                        'court_number' => ($i % 6) + 1,
                        'status' => $completed ? MatchStatus::COMPLETED->value : MatchStatus::SCHEDULED->value,
                        'tournament_day' => \App\Helpers\TournamentDayHelper::calculateTournamentDay(1, 'single_elimination', $numRounds),
                        'winner_id' => $completed ? $teamA[0]->id : null,
                        'winner_partner_id' => $completed ? $teamA[1]->id : null,
                    ]
                );

                if ($completed) {
                    // Create match result
                    $result = MatchResult::updateOrCreate(
                        ['match_id' => $match->id],
                        [
                            'winner_id' => $teamA[0]->id,
                            'winner_partner_id' => $teamA[1]->id,
                            'player1_set1_score' => 21,
                            'player2_set1_score' => 16,
                            'player1_set2_score' => 21,
                            'player2_set2_score' => 14,
                            'score_inputted_by' => 'manager',
                            'inputted_by_user_id' => $managerId,
                            'elo_updated' => false,
                        ]
                    );
                    
                    // Update ELO ratings using EloRatingService (matches actual application flow)
                    $eloService = app(EloRatingService::class);
                    $eloService->calculateDoublesMatchRatings(
                        $teamA[0], $teamA[1], 
                        $teamB[0], $teamB[1], 
                        true, 
                        CategoryType::MENS_DOUBLES->value,
                        $tournament->id
                    );
                    
                    // Mark ELO as updated
                    $result->update(['elo_updated' => true]);
                }
            }
        }

        // Women's Doubles
        if (isset($catMap["Women's Doubles"])) {
            $teamCount = floor($participantCount / 2);
            $wdPlayers = $females->take($teamCount * 2)->values();
            $teams = [];
            for ($i = 0; $i < $teamCount * 2; $i += 2) {
                if (isset($wdPlayers[$i]) && isset($wdPlayers[$i + 1])) {
                    $teams[] = [$wdPlayers[$i], $wdPlayers[$i + 1]];
                }
            }
            $teams = collect($teams)->take($teamCount)->values();
            // Use TournamentRoundHelper for consistent round naming
            $numRounds = ceil(log($teamCount, 2));
            $doublesRoundName = \App\Helpers\TournamentRoundHelper::getRoundName('single_elimination', 1, $numRounds);

            foreach ($teams as $team) {
                TournamentRegistration::updateOrCreate(
                    ['tournament_id' => $tournament->id, 'category_id' => $catMap["Women's Doubles"]->id, 'player_id' => $team[0]->id],
                    ['partner_id' => $team[1]->id, 'status' => 'approved']
                );
            }

            $matchCount = floor($teamCount / 2);
            for ($i = 0; $i < $matchCount; $i++) {
                if (!isset($teams[$i * 2]) || !isset($teams[$i * 2 + 1])) continue;
                $teamA = $teams[$i * 2];
                $teamB = $teams[$i * 2 + 1];

                // For ongoing tournaments, create matches with varied statuses
                $completed = $i < 1;
                $canInputResult = $i >= 1 && $i < 3; // Matches 2-3 can have results input
                
                // Calculate match date and time
                $yesterday = Carbon::parse('2025-12-09');
                $matchDate = $startDate->copy()->addDay();
                $matchTime = $startDate->copy()->addDay()->setTime(14 + $i, 0);
                
                if ($completed || $canInputResult) {
                    // Schedule completed and input-result matches for Dec 9 (yesterday)
                    $matchDate = $yesterday;
                    if ($completed) {
                        // Completed match: scheduled earlier (2 PM)
                        $matchTime = $yesterday->copy()->setTime(14 + $i, 0);
                    } else {
                        // Matches that can have results input: scheduled later (3 PM, 4 PM)
                        $matchTime = $yesterday->copy()->setTime(15 + ($i - 1), 0);
                    }
                }
                
                $match = TournamentMatch::updateOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'tournament_category_id' => $catMap["Women's Doubles"]->id,
                        'round' => $doublesRoundName,
                        'match_number' => $i + 1,
                    ],
                    [
                        'player1_id' => $teamA[0]->id,
                        'player1_partner_id' => $teamA[1]->id,
                        'player2_id' => $teamB[0]->id,
                        'player2_partner_id' => $teamB[1]->id,
                        'scheduled_date' => $matchDate->toDateString(),
                        'scheduled_time' => $matchTime,
                        'court_number' => ($i % 6) + 1,
                        'status' => $completed ? MatchStatus::COMPLETED->value : MatchStatus::SCHEDULED->value,
                        'tournament_day' => \App\Helpers\TournamentDayHelper::calculateTournamentDay(1, 'single_elimination', $numRounds),
                        'winner_id' => $completed ? $teamA[0]->id : null,
                        'winner_partner_id' => $completed ? $teamA[1]->id : null,
                    ]
                );

                if ($completed) {
                    // Create match result
                    $result = MatchResult::updateOrCreate(
                        ['match_id' => $match->id],
                        [
                            'winner_id' => $teamA[0]->id,
                            'winner_partner_id' => $teamA[1]->id,
                            'player1_set1_score' => 21,
                            'player2_set1_score' => 15,
                            'player1_set2_score' => 21,
                            'player2_set2_score' => 17,
                            'score_inputted_by' => 'manager',
                            'inputted_by_user_id' => $managerId,
                            'elo_updated' => false,
                        ]
                    );
                    
                    // Update ELO ratings using EloRatingService (matches actual application flow)
                    $eloService = app(EloRatingService::class);
                    $eloService->calculateDoublesMatchRatings(
                        $teamA[0], $teamA[1], 
                        $teamB[0], $teamB[1], 
                        true, 
                        CategoryType::WOMENS_DOUBLES->value,
                        $tournament->id
                    );
                    
                    // Mark ELO as updated
                    $result->update(['elo_updated' => true]);
                }
            }
        }

        // Mixed Doubles
        if (isset($catMap["Mixed Doubles"])) {
            $teamCount = floor($participantCount / 2);
            $xdMales = $males->take($teamCount)->values();
            $xdFemales = $females->take($teamCount)->values();
            $teams = [];
            for ($i = 0; $i < $teamCount; $i++) {
                if (isset($xdMales[$i]) && isset($xdFemales[$i])) {
                    $teams[] = [$xdMales[$i], $xdFemales[$i]];
                }
            }
            $teams = collect($teams)->values();
            // Use TournamentRoundHelper for consistent round naming
            $numRounds = ceil(log($teamCount, 2));
            $doublesRoundName = \App\Helpers\TournamentRoundHelper::getRoundName('single_elimination', 1, $numRounds);

            foreach ($teams as $team) {
                TournamentRegistration::updateOrCreate(
                    ['tournament_id' => $tournament->id, 'category_id' => $catMap["Mixed Doubles"]->id, 'player_id' => $team[0]->id],
                    ['partner_id' => $team[1]->id, 'status' => 'approved']
                );
            }

            $matchCount = floor($teamCount / 2);
            for ($i = 0; $i < $matchCount; $i++) {
                if (!isset($teams[$i * 2]) || !isset($teams[$i * 2 + 1])) continue;
                $teamA = $teams[$i * 2];
                $teamB = $teams[$i * 2 + 1];

                // For ongoing tournaments, create matches with varied statuses
                $completed = $i < 1;
                $canInputResult = $i >= 1 && $i < 3; // Matches 2-3 can have results input
                
                // Calculate match date and time
                $yesterday = Carbon::parse('2025-12-09');
                $matchDate = $startDate->copy()->addDay();
                $matchTime = $startDate->copy()->addDay()->setTime(18 + $i, 0);
                
                if ($completed || $canInputResult) {
                    // Schedule completed and input-result matches for Dec 9 (yesterday)
                    $matchDate = $yesterday;
                    if ($completed) {
                        // Completed match: scheduled earlier (6 PM)
                        $matchTime = $yesterday->copy()->setTime(18 + $i, 0);
                    } else {
                        // Matches that can have results input: scheduled later (7 PM, 8 PM)
                        $matchTime = $yesterday->copy()->setTime(19 + ($i - 1), 0);
                    }
                }
                
                $match = TournamentMatch::updateOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'tournament_category_id' => $catMap["Mixed Doubles"]->id,
                        'round' => $doublesRoundName,
                        'match_number' => $i + 1,
                    ],
                    [
                        'player1_id' => $teamA[0]->id,
                        'player1_partner_id' => $teamA[1]->id,
                        'player2_id' => $teamB[0]->id,
                        'player2_partner_id' => $teamB[1]->id,
                        'scheduled_date' => $matchDate->toDateString(),
                        'scheduled_time' => $matchTime,
                        'court_number' => ($i % 6) + 1,
                        'status' => $completed ? MatchStatus::COMPLETED->value : MatchStatus::SCHEDULED->value,
                        'tournament_day' => \App\Helpers\TournamentDayHelper::calculateTournamentDay(1, 'single_elimination', $numRounds),
                        'winner_id' => $completed ? $teamA[0]->id : null,
                        'winner_partner_id' => $completed ? $teamA[1]->id : null,
                    ]
                );

                if ($completed) {
                    // Create match result
                    $result = MatchResult::updateOrCreate(
                        ['match_id' => $match->id],
                        [
                            'winner_id' => $teamA[0]->id,
                            'winner_partner_id' => $teamA[1]->id,
                            'player1_set1_score' => 21,
                            'player2_set1_score' => 19,
                            'player1_set2_score' => 21,
                            'player2_set2_score' => 16,
                            'score_inputted_by' => 'manager',
                            'inputted_by_user_id' => $managerId,
                            'elo_updated' => false,
                        ]
                    );
                    
                    // Update ELO ratings using EloRatingService (matches actual application flow)
                    $eloService = app(EloRatingService::class);
                    $eloService->calculateDoublesMatchRatings(
                        $teamA[0], $teamA[1], 
                        $teamB[0], $teamB[1], 
                        true, 
                        CategoryType::MIXED_DOUBLES->value,
                        $tournament->id
                    );
                    
                    // Mark ELO as updated
                    $result->update(['elo_updated' => true]);
                }
            }
        }
    }

    /**
     * Seed 2 completed tournaments per club (4 total)
     * - Completed: Men's/Women's Singles, Open to all skill levels, Round of 16
     * - Completed: Men's/Women's Doubles & Mixed Doubles, Open to all skill levels, Round of 32
     */
    private function seedCompletedTournaments(): void
    {
        $managers = [
            User::where('email', 'manager.real1@badmintourph.com')->first(),
            User::where('email', 'manager.real2@badmintourph.com')->first(),
        ];
        
        // Only use test players for seeding tournaments
        $players = User::where('role', 'player')
            ->whereIn('email', $this->getTestPlayerEmails())
            ->get();
        if ($players->count() < 32) {
            return;
        }

        foreach ($managers as $manager) {
            if (!$manager) continue;

            $club = Club::where('manager_id', $manager->id)->first();
            if (!$club) continue;

            $startDate = now()->subDays(30);
            $endDate = now()->subDays(23);

            $tournaments = [
                [
                    'name' => ($club->name === 'Manila Smash Club' ? 'Manila' : 'Quezon') . ' Singles Championship 2025',
                    'description' => 'Completed singles tournament showcasing top players in men\'s and women\'s divisions. Open to all skill levels. 16 slots per category.',
                    'categories' => ["Men's Singles", "Women's Singles"],
                ],
                [
                    'name' => ($club->name === 'Manila Smash Club' ? 'Manila' : 'Quezon') . ' Doubles & Mixed Championship 2025',
                    'description' => 'Completed doubles and mixed doubles tournament. Open to all skill levels. 32 slots per category.',
                    'categories' => ["Men's Doubles", "Women's Doubles", "Mixed Doubles"],
                ],
            ];

            foreach ($tournaments as $t) {
                $this->resetTournamentData($t['name']);

                $tournament = Tournament::updateOrCreate(
                    ['name' => $t['name']],
                    [
                        'description' => $t['description'],
                        'organizer_id' => $manager->id,
                        'club_id' => $club->id,
                        'type' => 'singles',
                        'venue_name' => $club->name === 'Manila Smash Club' ? 'Rizal Memorial Badminton Hall' : 'Quezon Sports Complex',
                        'number_of_courts' => 6,
                        'contact_email' => $manager->email,
                        'contact_phone' => '+63 917 555 ' . ($club->name === 'Manila Smash Club' ? '1234' : '5678'),
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString(),
                        'registration_deadline' => $startDate->copy()->subDays(7)->toDateString(),
                        'withdrawal_deadline' => $startDate->copy()->subDays(5),
                        'location' => $club->city . ', NCR',
                        'registration_fee' => 600,
                        'status' => TournamentStatus::COMPLETED->value,
                        'bracket_type' => 'single_elimination',
                        'archived' => false,
                    ]
                );

                // Create categories with appropriate slots:
                // Singles: 16 slots (8 males + 8 females per club = 16 total)
                // Doubles: 32 slots (16 teams = 32 players, need both clubs)
                $catMap = [];
                foreach ($t['categories'] as $catName) {
                    // Determine max_participants based on category type
                    $isSingles = in_array($catName, ["Men's Singles", "Women's Singles"]);
                    $maxParticipants = $isSingles ? 16 : 32;
                    
                    $catMap[$catName] = TournamentCategory::updateOrCreate(
                        ['tournament_id' => $tournament->id, 'name' => $catName],
                        [
                            'max_participants' => $maxParticipants,
                            'match_duration_minutes' => 21,
                            'break_between_matches_minutes' => 5,
                            'skill_level' => SkillLevel::OPEN->value,
                        ]
                    );
                }

                // Get players sorted by ELO (use club players if available)
                // For doubles categories (32 slots), we need players from both clubs
                // For singles categories (16 slots), club players are sufficient
                $clubPlayerIds = ClubPlayer::where('club_id', $club->id)
                    ->where('status', 'approved')
                    ->pluck('player_id')
                    ->toArray();
                
                $clubPlayers = $players->filter(fn($p) => in_array($p->id, $clubPlayerIds));
                
                // Use all players if club has less than 16, or if tournament has doubles categories (need 32 players)
                $hasDoubles = !empty(array_intersect($t['categories'], ["Men's Doubles", "Women's Doubles", "Mixed Doubles"]));
                if ($clubPlayers->count() < 16 || $hasDoubles) {
                    $clubPlayers = $players; // Use all players for doubles categories or if club is small
                }

                $males = $clubPlayers->filter(fn($p) => ucfirst(strtolower($p->gender ?? '')) === 'Male')
                    ->map(function($p) {
                        $elo = EloRating::where('player_id', $p->id)->where('category', CategoryType::MENS_SINGLES->value)->first();
                        $p->elo_rating = $elo?->current_rating ?? 1500;
                        return $p;
                    })
                    ->sortByDesc('elo_rating')
                    ->values();

                $females = $clubPlayers->filter(fn($p) => ucfirst(strtolower($p->gender ?? '')) === 'Female')
                    ->map(function($p) {
                        $elo = EloRating::where('player_id', $p->id)->where('category', CategoryType::WOMENS_SINGLES->value)->first();
                        $p->elo_rating = $elo?->current_rating ?? 1500;
                        return $p;
                    })
                    ->sortByDesc('elo_rating')
                    ->values();

                // Create completed brackets
                $this->createCompletedBrackets($tournament, $catMap, $males, $females, $manager->id, $startDate);
            }
        }
        
        // Update matches_played count in EloRating based on actual matches
        $this->updateMatchesPlayedCount();
    }

    /**
     * Create completed brackets with full match history
     */
    private function createCompletedBrackets($tournament, $catMap, $males, $females, $managerId, $startDate): void
    {
        // Men's Singles - Full bracket
        if (isset($catMap["Men's Singles"])) {
            $maxParticipants = $catMap["Men's Singles"]->max_participants;
            $participantCount = min($maxParticipants, $males->count());
            $players = $males->take($participantCount)->values();
            $this->createSinglesBracket($tournament->id, $catMap["Men's Singles"]->id, $players, $managerId, $startDate, CategoryType::MENS_SINGLES->value);
        }

        // Women's Singles
        if (isset($catMap["Women's Singles"])) {
            $maxParticipants = $catMap["Women's Singles"]->max_participants;
            $participantCount = min($maxParticipants, $females->count());
            $players = $females->take($participantCount)->values();
            $this->createSinglesBracket($tournament->id, $catMap["Women's Singles"]->id, $players, $managerId, $startDate, CategoryType::WOMENS_SINGLES->value);
        }

        // Men's Doubles - Full bracket
        if (isset($catMap["Men's Doubles"])) {
            $maxParticipants = $catMap["Men's Doubles"]->max_participants;
            $participantCount = min($maxParticipants, $males->count());
            $teamCount = floor($participantCount / 2);
            $mdPlayers = $males->take($teamCount * 2)->values();
            $teams = [];
            for ($i = 0; $i < $teamCount * 2; $i += 2) {
                if (isset($mdPlayers[$i]) && isset($mdPlayers[$i + 1])) {
                    $teams[] = [$mdPlayers[$i], $mdPlayers[$i + 1]];
                }
            }
            $teams = collect($teams)->take($teamCount)->values();
            $this->createDoublesBracket($tournament->id, $catMap["Men's Doubles"]->id, $teams, $managerId, $startDate, CategoryType::MENS_DOUBLES->value);
        }

        // Women's Doubles
        if (isset($catMap["Women's Doubles"])) {
            $maxParticipants = $catMap["Women's Doubles"]->max_participants;
            $participantCount = min($maxParticipants, $females->count());
            $teamCount = floor($participantCount / 2);
            $wdPlayers = $females->take($teamCount * 2)->values();
            $teams = [];
            for ($i = 0; $i < $teamCount * 2; $i += 2) {
                if (isset($wdPlayers[$i]) && isset($wdPlayers[$i + 1])) {
                    $teams[] = [$wdPlayers[$i], $wdPlayers[$i + 1]];
                }
            }
            $teams = collect($teams)->take($teamCount)->values();
            $this->createDoublesBracket($tournament->id, $catMap["Women's Doubles"]->id, $teams, $managerId, $startDate, CategoryType::WOMENS_DOUBLES->value);
        }

        // Mixed Doubles
        if (isset($catMap["Mixed Doubles"])) {
            $maxParticipants = $catMap["Mixed Doubles"]->max_participants;
            $participantCount = min($maxParticipants, min($males->count(), $females->count()));
            $teamCount = floor($participantCount / 2);
            $xdMales = $males->take($teamCount)->values();
            $xdFemales = $females->take($teamCount)->values();
            $teams = [];
            for ($i = 0; $i < $teamCount; $i++) {
                if (isset($xdMales[$i]) && isset($xdFemales[$i])) {
                    $teams[] = [$xdMales[$i], $xdFemales[$i]];
                }
            }
            $teams = collect($teams)->values();
            $this->createDoublesBracket($tournament->id, $catMap["Mixed Doubles"]->id, $teams, $managerId, $startDate, CategoryType::MIXED_DOUBLES->value);
        }
    }

    /**
     * Create a completed singles bracket (R32/R16 → QF → SF → Finals)
     */
    private function createSinglesBracket($tournamentId, $categoryId, $players, $managerId, $startDate, $categoryType = null): void
    {
        $playerCount = $players->count();
        if ($playerCount < 4) {
            return;
        }

        // Register all players
        foreach ($players as $p) {
            TournamentRegistration::updateOrCreate(
                ['tournament_id' => $tournamentId, 'category_id' => $categoryId, 'player_id' => $p->id],
                ['status' => 'approved']
            );
        }

        // Determine rounds based on player count using TournamentRoundHelper
        $numRounds = ceil(log($playerCount, 2));
        $rounds = [];
        $remainingPlayers = $playerCount;
        for ($i = 1; $i <= $numRounds; $i++) {
            $roundName = \App\Helpers\TournamentRoundHelper::getRoundName('single_elimination', $i, $numRounds);
            $matchesInRound = ceil($remainingPlayers / 2);
            $rounds[] = [
                'name' => $roundName,
                'count' => $matchesInRound,
                'day' => $i - 1,
            ];
            $remainingPlayers = $matchesInRound;
        }

        $winners = $players->values();
        $matchNum = 1;

        foreach ($rounds as $round) {
            $nextWinners = collect();
            $pairs = [];

            // Create pairs for this round
            for ($i = 0; $i < $round['count']; $i++) {
                $idx1 = $i * 2;
                $idx2 = $i * 2 + 1;
                if (isset($winners[$idx1]) && isset($winners[$idx2])) {
                    $pairs[] = [$winners[$idx1], $winners[$idx2]];
                }
            }

            // Create matches
            foreach ($pairs as $idx => $pair) {
                $p1 = $pair[0];
                $p2 = $pair[1];
                $p1Elo = $this->getPlayerElo($p1, $categoryType);
                $p2Elo = $this->getPlayerElo($p2, $categoryType);
                
                $isWalkover = ($idx === count($pairs) - 1 && $round['name'] === 'Round of 16');
                $winner = $p1Elo >= $p2Elo ? $p1 : $p2;
                $isCloseMatch = ($idx % 3 === 0);
                $needsThirdSet = ($idx % 4 === 0 && !$isWalkover);

                $match = TournamentMatch::updateOrCreate(
                    [
                        'tournament_id' => $tournamentId,
                        'tournament_category_id' => $categoryId,
                        'round' => $round['name'],
                        'match_number' => $matchNum++,
                    ],
                    [
                        'player1_id' => $p1->id,
                        'player2_id' => $p2->id,
                        'winner_id' => $winner->id,
                        'scheduled_date' => $startDate->copy()->addDays($round['day'])->toDateString(),
                        'scheduled_time' => $startDate->copy()->addDays($round['day'])->setTime(10 + $idx, 0),
                        'court_number' => ($idx % 6) + 1,
                        'status' => MatchStatus::COMPLETED->value,
                        'tournament_day' => TournamentDayHelper::calculateTournamentDay($round['day'] + 1, 'single_elimination', $numRounds),
                    ]
                );

                if ($isWalkover) {
                    $result = MatchResult::updateOrCreate(
                        ['match_id' => $match->id],
                        [
                            'winner_id' => $winner->id,
                            'player1_set1_score' => 0,
                            'player2_set1_score' => 0,
                            'player1_set2_score' => 0,
                            'player2_set2_score' => 0,
                            'score_inputted_by' => 'manager',
                            'inputted_by_user_id' => $managerId,
                            'is_walkover' => true,
                            'elo_updated' => false,
                        ]
                    );
                    
                    $eloService = app(EloRatingService::class);
                    $loser = $winner->id === $p1->id ? $p2 : $p1;
                    $eloService->applyWalkoverPenalty($loser, $categoryType);
                    $result->update(['elo_updated' => true]);
                } else {
                    $p1Wins = $winner->id === $p1->id;
                    if ($needsThirdSet) {
                        $set1Winner = $p1Wins;
                        $set2Winner = !$p1Wins;
                        $result = MatchResult::updateOrCreate(
                            ['match_id' => $match->id],
                            [
                                'winner_id' => $winner->id,
                                'player1_set1_score' => $set1Winner ? 21 : ($isCloseMatch ? 19 : 17),
                                'player2_set1_score' => $set1Winner ? ($isCloseMatch ? 19 : 17) : 21,
                                'player1_set2_score' => $set2Winner ? 21 : ($isCloseMatch ? 19 : 17),
                                'player2_set2_score' => $set2Winner ? ($isCloseMatch ? 19 : 17) : 21,
                                'player1_set3_score' => $p1Wins ? 21 : ($isCloseMatch ? 19 : 17),
                                'player2_set3_score' => $p1Wins ? ($isCloseMatch ? 19 : 17) : 21,
                                'score_inputted_by' => 'manager',
                                'inputted_by_user_id' => $managerId,
                                'elo_updated' => false,
                            ]
                        );
                    } else {
                        $score1 = $isCloseMatch ? 21 : ($p1Wins ? 21 : 18);
                        $score2 = $isCloseMatch ? 19 : ($p1Wins ? 18 : 21);
                        $score3 = $isCloseMatch ? 21 : ($p1Wins ? 21 : 17);
                        $score4 = $isCloseMatch ? 19 : ($p1Wins ? 17 : 21);
                        
                        if (!$p1Wins) {
                            $temp = $score1;
                            $score1 = $score2;
                            $score2 = $temp;
                            $temp = $score3;
                            $score3 = $score4;
                            $score4 = $temp;
                        }
                        
                        $result = MatchResult::updateOrCreate(
                            ['match_id' => $match->id],
                            [
                                'winner_id' => $winner->id,
                                'player1_set1_score' => $score1,
                                'player2_set1_score' => $score2,
                                'player1_set2_score' => $score3,
                                'player2_set2_score' => $score4,
                                'score_inputted_by' => 'manager',
                                'inputted_by_user_id' => $managerId,
                                'elo_updated' => false,
                            ]
                        );
                    }
                    
                    $eloService = app(EloRatingService::class);
                    $eloService->calculateMatchRatings($p1, $p2, $p1Wins, $categoryType, $tournamentId);
                    $result->update(['elo_updated' => true]);
                }

                $nextWinners->push($winner);
            }

            $winners = $nextWinners;
        }
    }

    /**
     * Create a completed doubles bracket (R16/QF → SF → Finals)
     */
    private function createDoublesBracket($tournamentId, $categoryId, $teams, $managerId, $startDate, $categoryType = null): void
    {
        $teamCount = $teams->count();
        if ($teamCount < 2) {
            return;
        }

        // Register all teams
        foreach ($teams as $team) {
            TournamentRegistration::updateOrCreate(
                ['tournament_id' => $tournamentId, 'category_id' => $categoryId, 'player_id' => $team[0]->id],
                ['partner_id' => $team[1]->id, 'status' => 'approved']
            );
        }

        // Determine rounds based on team count using TournamentRoundHelper
        $numRounds = ceil(log($teamCount, 2));
        $rounds = [];
        $remainingTeams = $teamCount;
        for ($i = 1; $i <= $numRounds; $i++) {
            $roundName = \App\Helpers\TournamentRoundHelper::getRoundName('single_elimination', $i, $numRounds);
            $matchesInRound = ceil($remainingTeams / 2);
            $rounds[] = [
                'name' => $roundName,
                'count' => $matchesInRound,
                'day' => $i - 1,
            ];
            $remainingTeams = $matchesInRound;
        }

        $winners = $teams->values();
        $matchNum = 1;

        foreach ($rounds as $round) {
            $nextWinners = collect();
            $pairs = [];

            // Create pairs for this round
            for ($i = 0; $i < $round['count']; $i++) {
                $idx1 = $i * 2;
                $idx2 = $i * 2 + 1;
                if (isset($winners[$idx1]) && isset($winners[$idx2])) {
                    $pairs[] = [$winners[$idx1], $winners[$idx2]];
                }
            }

            // Create matches
            foreach ($pairs as $idx => $pair) {
                $teamA = $pair[0];
                $teamB = $pair[1];
                $teamAElo = ($this->getPlayerElo($teamA[0], $categoryType) + $this->getPlayerElo($teamA[1], $categoryType)) / 2;
                $teamBElo = ($this->getPlayerElo($teamB[0], $categoryType) + $this->getPlayerElo($teamB[1], $categoryType)) / 2;
                
                $isWalkover = ($idx === count($pairs) - 1 && $round['name'] === 'Quarterfinals');
                $winner = $teamAElo >= $teamBElo ? $teamA : $teamB;
                $teamAWins = $winner === $teamA;
                $isCloseMatch = ($idx % 3 === 0);
                $needsThirdSet = ($idx % 4 === 0 && !$isWalkover);

                $match = TournamentMatch::updateOrCreate(
                    [
                        'tournament_id' => $tournamentId,
                        'tournament_category_id' => $categoryId,
                        'round' => $round['name'],
                        'match_number' => $matchNum++,
                    ],
                    [
                        'player1_id' => $teamA[0]->id,
                        'player1_partner_id' => $teamA[1]->id,
                        'player2_id' => $teamB[0]->id,
                        'player2_partner_id' => $teamB[1]->id,
                        'winner_id' => $winner[0]->id,
                        'winner_partner_id' => $winner[1]->id,
                        'scheduled_date' => $startDate->copy()->addDays($round['day'])->toDateString(),
                        'scheduled_time' => $startDate->copy()->addDays($round['day'])->setTime(11 + $idx, 0),
                        'court_number' => ($idx % 6) + 1,
                        'status' => MatchStatus::COMPLETED->value,
                        'tournament_day' => TournamentDayHelper::calculateTournamentDay($round['day'] + 1, 'single_elimination', $numRounds),
                    ]
                );

                if ($isWalkover) {
                    $result = MatchResult::updateOrCreate(
                        ['match_id' => $match->id],
                        [
                            'winner_id' => $winner[0]->id,
                            'winner_partner_id' => $winner[1]->id,
                            'player1_set1_score' => 0,
                            'player2_set1_score' => 0,
                            'player1_set2_score' => 0,
                            'player2_set2_score' => 0,
                            'score_inputted_by' => 'manager',
                            'inputted_by_user_id' => $managerId,
                            'is_walkover' => true,
                            'elo_updated' => false,
                        ]
                    );
                    
                    $eloService = app(EloRatingService::class);
                    $loserTeam = $teamAWins ? $teamB : $teamA;
                    $eloService->applyWalkoverPenalty($loserTeam[0], $categoryType);
                    $eloService->applyWalkoverPenalty($loserTeam[1], $categoryType);
                    $result->update(['elo_updated' => true]);
                } else {
                    if ($needsThirdSet) {
                        $set1Winner = $teamAWins;
                        $set2Winner = !$teamAWins;
                        $result = MatchResult::updateOrCreate(
                            ['match_id' => $match->id],
                            [
                                'winner_id' => $winner[0]->id,
                                'winner_partner_id' => $winner[1]->id,
                                'player1_set1_score' => $set1Winner ? 21 : ($isCloseMatch ? 19 : 17),
                                'player2_set1_score' => $set1Winner ? ($isCloseMatch ? 19 : 17) : 21,
                                'player1_set2_score' => $set2Winner ? 21 : ($isCloseMatch ? 19 : 17),
                                'player2_set2_score' => $set2Winner ? ($isCloseMatch ? 19 : 17) : 21,
                                'player1_set3_score' => $teamAWins ? 21 : ($isCloseMatch ? 19 : 17),
                                'player2_set3_score' => $teamAWins ? ($isCloseMatch ? 19 : 17) : 21,
                                'score_inputted_by' => 'manager',
                                'inputted_by_user_id' => $managerId,
                                'elo_updated' => false,
                            ]
                        );
                    } else {
                        $score1 = $isCloseMatch ? 21 : ($teamAWins ? 21 : 18);
                        $score2 = $isCloseMatch ? 19 : ($teamAWins ? 18 : 21);
                        $score3 = $isCloseMatch ? 21 : ($teamAWins ? 21 : 17);
                        $score4 = $isCloseMatch ? 19 : ($teamAWins ? 17 : 21);
                        
                        if (!$teamAWins) {
                            $temp = $score1;
                            $score1 = $score2;
                            $score2 = $temp;
                            $temp = $score3;
                            $score3 = $score4;
                            $score4 = $temp;
                        }
                        
                        $result = MatchResult::updateOrCreate(
                            ['match_id' => $match->id],
                            [
                                'winner_id' => $winner[0]->id,
                                'winner_partner_id' => $winner[1]->id,
                                'player1_set1_score' => $score1,
                                'player2_set1_score' => $score2,
                                'player1_set2_score' => $score3,
                                'player2_set2_score' => $score4,
                                'score_inputted_by' => 'manager',
                                'inputted_by_user_id' => $managerId,
                                'elo_updated' => false,
                            ]
                        );
                    }
                    
                    $eloService = app(EloRatingService::class);
                    $eloService->calculateDoublesMatchRatings(
                        $teamA[0], $teamA[1], 
                        $teamB[0], $teamB[1], 
                        $teamAWins, 
                        $categoryType,
                        $tournamentId
                    );
                    $result->update(['elo_updated' => true]);
                }

                $nextWinners->push($winner);
            }

            $winners = $nextWinners;
        }
    }

    /**
     * Reset tournament data before reseeding
     */
    private function resetTournamentData(string $tournamentName): void
    {
        $tournament = Tournament::where('name', $tournamentName)->first();
        if (!$tournament) {
            return;
        }

        $matchIds = TournamentMatch::where('tournament_id', $tournament->id)->pluck('id');
        if ($matchIds->isNotEmpty()) {
            MatchResult::whereIn('match_id', $matchIds)->delete();
        }

        TournamentMatch::where('tournament_id', $tournament->id)->delete();
        TournamentRegistration::where('tournament_id', $tournament->id)->delete();
        TournamentCategory::where('tournament_id', $tournament->id)->delete();
    }

    /**
     * Fix round names for round robin tournaments
     * This corrects matches that have incorrect round numbers (e.g., Round 5-15 instead of Round 1-11)
     */
    private function fixRoundRobinRoundNames(Tournament $tournament): void
    {
        if ($tournament->bracket_type !== 'round_robin') {
            return;
        }

        $matchesByCategory = TournamentMatch::where('tournament_id', $tournament->id)
            ->with('category')
            ->get()
            ->groupBy('tournament_category_id');

        foreach ($matchesByCategory as $categoryId => $matches) {
            $matchesByRound = $matches->groupBy('round');
            
            $roundNumbers = [];
            $roundRobinRounds = [];
            $eliminationRounds = [];
            
            foreach ($matchesByRound->keys() as $roundName) {
                $r = strtolower(trim((string)$roundName));
                
                if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                    $roundNum = (int)$m[1];
                    $roundNumbers[] = $roundNum;
                    $roundRobinRounds[$roundName] = $roundNum;
                } elseif (preg_match('/round\s+of\s+\d+/i', $r) || 
                          str_contains($r, 'quarter') || 
                          str_contains($r, 'semi') || 
                          (str_contains($r, 'final') && !str_contains($r, 'semi'))) {
                    $eliminationRounds[] = $roundName;
                }
            }
            
            if (empty($roundNumbers) && empty($eliminationRounds)) {
                continue;
            }
            
            if (!empty($eliminationRounds)) {
                foreach ($eliminationRounds as $elimRound) {
                    TournamentMatch::where('tournament_id', $tournament->id)
                        ->where('tournament_category_id', $categoryId)
                        ->where('round', $elimRound)
                        ->delete();
                }
            }
            
            if (!empty($roundNumbers)) {
                $sortedRoundNumbers = array_unique($roundNumbers);
                sort($sortedRoundNumbers);
                
                $roundMapping = [];
                $newRoundNum = 1;
                
                foreach ($sortedRoundNumbers as $oldRoundNum) {
                    $oldRoundName = null;
                    foreach ($roundRobinRounds as $name => $num) {
                        if ($num === $oldRoundNum) {
                            $oldRoundName = $name;
                            break;
                        }
                    }
                    
                    if ($oldRoundName) {
                        $newRoundName = "Round {$newRoundNum}";
                        if ($oldRoundName !== $newRoundName) {
                            $roundMapping[$oldRoundName] = $newRoundName;
                        }
                        $newRoundNum++;
                    }
                }
                
                foreach ($roundMapping as $oldRound => $newRound) {
                    TournamentMatch::where('tournament_id', $tournament->id)
                        ->where('tournament_category_id', $categoryId)
                        ->where('round', $oldRound)
                        ->update(['round' => $newRound]);
                }
            }
        }
    }

    private function seedRoundRobinTournament(): void
    {
        $manager = User::where('email', 'manager.real1@badmintourph.com')->first();
        if (!$manager) return;

        $club = Club::where('manager_id', $manager->id)->first();
        if (!$club) return;

        $players = User::where('role', 'player')
            ->whereIn('email', $this->getTestPlayerEmails())
            ->get();
        if ($players->count() < 16) return;

        $this->resetTournamentData('Round Robin Championship 2025');

        $tournament = Tournament::updateOrCreate(
            ['name' => 'Round Robin Championship 2025'],
            [
                'description' => 'Round robin format tournament with all categories. Each participant plays all others once. Perfect for testing round robin bracket generation and standings.',
                'organizer_id' => $manager->id,
                'club_id' => $club->id,
                'type' => 'singles',
                'venue_name' => 'Manila Badminton Center',
                'number_of_courts' => 4,
                'contact_email' => $manager->email,
                'contact_phone' => '+63 917 555 1234',
                'start_date' => now()->addDays(7)->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(),
                'registration_deadline' => now()->addDays(4)->toDateString(),
                'withdrawal_deadline' => now()->addDays(6),
                'location' => 'Makati City, NCR',
                'registration_fee' => 500,
                'tournament_fee' => 500,
                'status' => TournamentStatus::PUBLISHED->value,
                'bracket_type' => 'round_robin',
                'archived' => false,
            ]
        );

        $categories = ["Men's Singles", "Women's Singles", "Men's Doubles", "Women's Doubles", "Mixed Doubles"];
        $catMap = [];

        foreach ($categories as $catName) {
            $isSingles = in_array($catName, ["Men's Singles", "Women's Singles"]);
            $maxParticipants = $isSingles ? 8 : 8;

            $catMap[$catName] = TournamentCategory::updateOrCreate(
                ['tournament_id' => $tournament->id, 'name' => $catName],
                [
                    'max_participants' => $maxParticipants,
                    'match_duration_minutes' => $isSingles ? 45 : 60,
                    'break_between_matches_minutes' => 5,
                    'skill_level' => SkillLevel::OPEN->value,
                    'schedule_start_time' => '09:00:00',
                    'schedule_start_date' => $tournament->start_date,
                ]
            );
        }

        $males = $players->filter(fn($p) => ucfirst(strtolower($p->gender ?? '')) === 'Male')->take(8)->values();
        $females = $players->filter(fn($p) => ucfirst(strtolower($p->gender ?? '')) === 'Female')->take(8)->values();

        if (isset($catMap["Men's Singles"])) {
            foreach ($males->take(8) as $p) {
                TournamentRegistration::updateOrCreate(
                    ['tournament_id' => $tournament->id, 'category_id' => $catMap["Men's Singles"]->id, 'player_id' => $p->id],
                    ['status' => 'approved']
                );
            }
        }

        if (isset($catMap["Women's Singles"])) {
            foreach ($females->take(8) as $p) {
                TournamentRegistration::updateOrCreate(
                    ['tournament_id' => $tournament->id, 'category_id' => $catMap["Women's Singles"]->id, 'player_id' => $p->id],
                    ['status' => 'approved']
                );
            }
        }

        if (isset($catMap["Men's Doubles"])) {
            $mdMales = $males->take(8)->values();
            for ($i = 0; $i < 4; $i++) {
                $p1 = $mdMales[$i * 2] ?? null;
                $p2 = $mdMales[$i * 2 + 1] ?? null;
                if ($p1 && $p2) {
                    TournamentRegistration::updateOrCreate(
                        ['tournament_id' => $tournament->id, 'category_id' => $catMap["Men's Doubles"]->id, 'player_id' => $p1->id],
                        ['partner_id' => $p2->id, 'status' => 'approved']
                    );
                }
            }
        }

        if (isset($catMap["Women's Doubles"])) {
            $wdFemales = $females->take(8)->values();
            for ($i = 0; $i < 4; $i++) {
                $p1 = $wdFemales[$i * 2] ?? null;
                $p2 = $wdFemales[$i * 2 + 1] ?? null;
                if ($p1 && $p2) {
                    TournamentRegistration::updateOrCreate(
                        ['tournament_id' => $tournament->id, 'category_id' => $catMap["Women's Doubles"]->id, 'player_id' => $p1->id],
                        ['partner_id' => $p2->id, 'status' => 'approved']
                    );
                }
            }
        }

        if (isset($catMap["Mixed Doubles"])) {
            $xdMales = $males->take(4)->values();
            $xdFemales = $females->take(4)->values();
            for ($i = 0; $i < 4; $i++) {
                $male = $xdMales[$i] ?? null;
                $female = $xdFemales[$i] ?? null;
                if ($male && $female) {
                    TournamentRegistration::updateOrCreate(
                        ['tournament_id' => $tournament->id, 'category_id' => $catMap["Mixed Doubles"]->id, 'player_id' => $male->id],
                        ['partner_id' => $female->id, 'status' => 'approved']
                    );
                }
            }
        }
    }

    /**
     * Create results for all round robin matches in a completed tournament
     */
    private function createCompletedRoundRobinResults($tournament, $catMap, $males, $females, $managerId, $startDate): void
    {
        $matches = TournamentMatch::where('tournament_id', $tournament->id)
            ->with(['player1', 'player2', 'player1Partner', 'player2Partner', 'category'])
            ->get();

        foreach ($matches as $match) {
            if ($match->result) {
                continue;
            }

            $category = $match->category;
            if (!$category) continue;

            $categoryTypeCode = strtoupper($category->type ?? 'MS');
            $categoryType = CategoryType::MENS_SINGLES->value;
            if ($categoryTypeCode === 'WS') {
                $categoryType = CategoryType::WOMENS_SINGLES->value;
            } elseif ($categoryTypeCode === 'MD') {
                $categoryType = CategoryType::MENS_DOUBLES->value;
            } elseif ($categoryTypeCode === 'WD') {
                $categoryType = CategoryType::WOMENS_DOUBLES->value;
            } elseif ($categoryTypeCode === 'XD') {
                $categoryType = CategoryType::MIXED_DOUBLES->value;
            }
            $isDoubles = CategoryType::from($categoryType)->isDoubles();

            $p1 = $match->player1;
            $p2 = $match->player2;
            if (!$p1 || !$p2) continue;

            $winner = $p1;
            $winnerPartnerId = null;
            $loserPartnerId = null;

            if ($isDoubles) {
                $p1p = $match->player1Partner;
                $p2p = $match->player2Partner;
                if (!$p1p || !$p2p) continue;

                $team1Elo = ($this->getPlayerElo($p1, $categoryType) + $this->getPlayerElo($p1p, $categoryType)) / 2;
                $team2Elo = ($this->getPlayerElo($p2, $categoryType) + $this->getPlayerElo($p2p, $categoryType)) / 2;

                if ($team1Elo >= $team2Elo) {
                    $winner = $p1;
                    $winnerPartnerId = $p1p->id;
                    $loserPartnerId = $p2p->id;
                } else {
                    $winner = $p2;
                    $winnerPartnerId = $p2p->id;
                    $loserPartnerId = $p1p->id;
                }
            } else {
                $p1Elo = $this->getPlayerElo($p1, $categoryType);
                $p2Elo = $this->getPlayerElo($p2, $categoryType);
                if ($p2Elo > $p1Elo) {
                    $winner = $p2;
                }
            }

            $matchIndex = $matches->search($match);
            $isWalkover = ($matchIndex % 15 === 0);
            $isCloseMatch = ($matchIndex % 5 === 0);
            $needsThirdSet = ($matchIndex % 7 === 0 && !$isWalkover);
            
            if ($isWalkover) {
                $result = MatchResult::updateOrCreate(
                    ['match_id' => $match->id],
                    [
                        'winner_id' => $winner->id,
                        'winner_partner_id' => $winnerPartnerId,
                        'player1_set1_score' => 0,
                        'player2_set1_score' => 0,
                        'player1_set2_score' => 0,
                        'player2_set2_score' => 0,
                        'score_inputted_by' => 'manager',
                        'inputted_by_user_id' => $managerId,
                        'is_walkover' => true,
                        'elo_updated' => false,
                    ]
                );
                
                $eloService = app(EloRatingService::class);
                if ($isDoubles) {
                    $loserTeam = $winner->id === $p1->id ? [$p2, $p2p] : [$p1, $p1p];
                    $eloService->applyWalkoverPenalty($loserTeam[0], $categoryType);
                    if ($loserTeam[1]) {
                        $eloService->applyWalkoverPenalty($loserTeam[1], $categoryType);
                    }
                } else {
                    $loser = $winner->id === $p1->id ? $p2 : $p1;
                    $eloService->applyWalkoverPenalty($loser, $categoryType);
                }
                $result->update(['elo_updated' => true]);
            } else {
                $p1Wins = $winner->id === $p1->id;
                if ($needsThirdSet) {
                    $set1Winner = $p1Wins;
                    $set2Winner = !$p1Wins;
                    $result = MatchResult::updateOrCreate(
                        ['match_id' => $match->id],
                        [
                            'winner_id' => $winner->id,
                            'winner_partner_id' => $winnerPartnerId,
                            'player1_set1_score' => $set1Winner ? 21 : ($isCloseMatch ? 19 : 17),
                            'player2_set1_score' => $set1Winner ? ($isCloseMatch ? 19 : 17) : 21,
                            'player1_set2_score' => $set2Winner ? 21 : ($isCloseMatch ? 19 : 17),
                            'player2_set2_score' => $set2Winner ? ($isCloseMatch ? 19 : 17) : 21,
                            'player1_set3_score' => $p1Wins ? 21 : ($isCloseMatch ? 19 : 17),
                            'player2_set3_score' => $p1Wins ? ($isCloseMatch ? 19 : 17) : 21,
                            'score_inputted_by' => 'manager',
                            'inputted_by_user_id' => $managerId,
                            'elo_updated' => false,
                        ]
                    );
                } else {
                    $score1 = $isCloseMatch ? 21 : ($p1Wins ? 21 : 18);
                    $score2 = $isCloseMatch ? 19 : ($p1Wins ? 18 : 21);
                    $score3 = $isCloseMatch ? 21 : ($p1Wins ? 21 : 17);
                    $score4 = $isCloseMatch ? 19 : ($p1Wins ? 17 : 21);
                    
                    if (!$p1Wins) {
                        $temp = $score1;
                        $score1 = $score2;
                        $score2 = $temp;
                        $temp = $score3;
                        $score3 = $score4;
                        $score4 = $temp;
                    }
                    
                    $result = MatchResult::updateOrCreate(
                        ['match_id' => $match->id],
                        [
                            'winner_id' => $winner->id,
                            'winner_partner_id' => $winnerPartnerId,
                            'player1_set1_score' => $score1,
                            'player2_set1_score' => $score2,
                            'player1_set2_score' => $score3,
                            'player2_set2_score' => $score4,
                            'score_inputted_by' => 'manager',
                            'inputted_by_user_id' => $managerId,
                            'elo_updated' => false,
                        ]
                    );
                }

                if ($isDoubles) {
                    $p1p = $match->player1Partner;
                    $p2p = $match->player2Partner;
                    $eloService = app(EloRatingService::class);
                    $eloService->calculateDoublesMatchRatings(
                        $p1, $p1p,
                        $p2, $p2p,
                        $p1Wins,
                        $categoryType,
                        $tournament->id
                    );
                } else {
                    $eloService = app(EloRatingService::class);
                    $eloService->calculateMatchRatings(
                        $p1, $p2,
                        $p1Wins,
                        $categoryType,
                        $tournament->id
                    );
                }

                $result->update(['elo_updated' => true]);
            }

            $match->update([
                'status' => MatchStatus::COMPLETED->value,
                'winner_id' => $winner->id,
                'winner_partner_id' => $winnerPartnerId,
            ]);
        }
    }

    /**
     * Get player ELO rating for a category
     */
    private function getPlayerElo($player, $categoryType): float
    {
        $elo = EloRating::where('player_id', $player->id)
            ->where('category', $categoryType)
            ->first();
        return $elo?->current_rating ?? 1500;
    }

    /**
     * Update matches_played count in EloRating based on actual completed matches
     */
    private function updateMatchesPlayedCount(): void
    {
        // Get all completed matches with results
        $completedMatches = TournamentMatch::where('status', MatchStatus::COMPLETED->value)
            ->whereHas('result')
            ->with(['category', 'result'])
            ->get();

        // Count matches per player per category
        $playerCategoryMatches = [];

        foreach ($completedMatches as $match) {
            if (!$match->category || !$match->result) continue;

            $categoryType = $match->category->type;
            $isDoubles = in_array($categoryType, [CategoryType::MENS_DOUBLES->value, CategoryType::WOMENS_DOUBLES->value, CategoryType::MIXED_DOUBLES->value]);

            // Count for player1
            if ($match->player1_id) {
                $key = $match->player1_id . '_' . $categoryType;
                $playerCategoryMatches[$key] = ($playerCategoryMatches[$key] ?? 0) + 1;
            }

            // Count for player2
            if ($match->player2_id) {
                $key = $match->player2_id . '_' . $categoryType;
                $playerCategoryMatches[$key] = ($playerCategoryMatches[$key] ?? 0) + 1;
            }

            // Count for doubles partners
            if ($isDoubles) {
                if ($match->player1_partner_id) {
                    $key = $match->player1_partner_id . '_' . $categoryType;
                    $playerCategoryMatches[$key] = ($playerCategoryMatches[$key] ?? 0) + 1;
                }
                if ($match->player2_partner_id) {
                    $key = $match->player2_partner_id . '_' . $categoryType;
                    $playerCategoryMatches[$key] = ($playerCategoryMatches[$key] ?? 0) + 1;
                }
            }
        }

        // Update EloRating matches_played
        foreach ($playerCategoryMatches as $key => $count) {
            [$playerId, $categoryType] = explode('_', $key);
            EloRating::where('player_id', $playerId)
                ->where('category', $categoryType)
                ->update(['matches_played' => $count]);
        }
    }
}
