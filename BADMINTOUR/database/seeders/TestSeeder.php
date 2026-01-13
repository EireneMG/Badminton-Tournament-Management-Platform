<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Club;
use App\Models\ClubPlayer;
use App\Models\EloRating;
use App\Models\ManagerIdVerification;
use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\TournamentRegistration;
use App\Models\TournamentMatch;
use App\Models\MatchResult;
use App\Models\WithdrawalRequest;
use App\Models\Notification;
use App\Models\PartnerInvitation;
use App\Models\RankingHistory;
use App\Services\MatchGenerationService;
use App\Services\EloRatingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates simple but complete test data:
     * - 2 clubs with 2 managers
     * - 20 players (10 per club) - enough for all scenarios
     * - 6 tournaments (3 per club):
     *   - Club 1: Singles (upcoming), Doubles (ongoing), All Categories (completed)
     *   - Club 2: Singles (ongoing), Doubles (completed), All Categories (upcoming)
     */
    public function run(): void
    {
        // Set timezone to Philippines
        date_default_timezone_set('Asia/Manila');
        Carbon::setLocale('en');
        $now = Carbon::now('Asia/Manila');
        
        $this->command->info('Creating test data...');
        $this->command->info('Current time: ' . $now->format('Y-m-d H:i:s'));
        
        // Check if test data already exists
        $existingManager = User::where('email', 'alex.rodriguez@test.com')->first();
        if ($existingManager) {
            $this->command->warn('Test data already exists. Skipping seeder.');
            $this->command->warn('To recreate test data, please clear the database first or delete existing test users.');
            return;
        }

        // ============================================
        // CREATE 2 CLUB MANAGERS
        // ============================================
        $managers = [];
        $managers[] = User::create([
            'name' => 'Alex Rodriguez',
            'first_name' => 'Alex',
            'middle_name' => '',
            'last_name' => 'Rodriguez',
            'email' => 'alex.rodriguez@test.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'contact_number' => '09171234567',
            'verification_status' => 'verified',
            'email_verified_at' => now(),
        ]);

        $managers[] = User::create([
            'name' => 'Sarah Chen',
            'first_name' => 'Sarah',
            'middle_name' => '',
            'last_name' => 'Chen',
            'email' => 'sarah.chen@test.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'contact_number' => '09181234567',
            'verification_status' => 'verified',
            'email_verified_at' => now(),
        ]);

        foreach ($managers as $manager) {
            ManagerIdVerification::create([
                'manager_id' => $manager->id,
                'id_type' => 'philsys_id',
                'id_file_path' => 'manager-ids/' . $manager->id . '_id.jpg',
                'status' => 'verified',
                'submitted_at' => Carbon::now()->subDays(30),
            ]);
        }

        $this->command->info('✓ Created 2 managers');

        // ============================================
        // CREATE 2 CLUBS
        // ============================================
        $clubs = [];
        $clubs[] = Club::create([
            'manager_id' => $managers[0]->id,
            'name' => 'Metro Manila Badminton Club',
            'description' => 'Premier badminton club in Metro Manila.',
            'province' => 'Metro Manila',
            'city' => 'Manila',
            'contact_email' => 'info@metromanilabc.com',
            'contact_phone' => '02-123-4567',
            'active' => true,
        ]);

        $clubs[] = Club::create([
            'manager_id' => $managers[1]->id,
            'name' => 'Cebu Elite Badminton Club',
            'description' => 'Elite badminton training facility in Cebu.',
            'province' => 'Cebu',
            'city' => 'Cebu City',
            'contact_email' => 'info@cebuelitebc.com',
            'contact_phone' => '032-555-6789',
            'active' => true,
        ]);

        $this->command->info('✓ Created 2 clubs');

        // ============================================
        // CREATE PLAYERS FOR ALL DIVISIONS
        // Junior: < 18 years old (birth_year >= 2007)
        // Senior: >= 18 years old (birth_year <= 2006)
        // Open: All ages (mix of both)
        // ============================================
        $players = [];
        $playerData = [
            // Club 1 - Senior Male players (age >= 18, birth_year <= 2006)
            ['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'gender' => 'male', 'email' => 'juan.delacruz@test.com', 'birth_year' => 2000],
            ['first_name' => 'Pedro', 'last_name' => 'Reyes', 'gender' => 'male', 'email' => 'pedro.reyes@test.com', 'birth_year' => 2001],
            ['first_name' => 'Jose', 'last_name' => 'Mendoza', 'gender' => 'male', 'email' => 'jose.mendoza@test.com', 'birth_year' => 2002],
            ['first_name' => 'Rafael', 'last_name' => 'Lopez', 'gender' => 'male', 'email' => 'rafael.lopez@test.com', 'birth_year' => 2003],
            ['first_name' => 'Andres', 'last_name' => 'Martinez', 'gender' => 'male', 'email' => 'andres.martinez@test.com', 'birth_year' => 2004],
            ['first_name' => 'Luis', 'last_name' => 'Herrera', 'gender' => 'male', 'email' => 'luis.herrera@test.com', 'birth_year' => 2005],
            ['first_name' => 'Sergio', 'last_name' => 'Morales', 'gender' => 'male', 'email' => 'sergio.morales@test.com', 'birth_year' => 2006],
            // Club 1 - Senior Female players
            ['first_name' => 'Maria', 'last_name' => 'Santos', 'gender' => 'female', 'email' => 'maria.santos@test.com', 'birth_year' => 2000],
            ['first_name' => 'Ana', 'last_name' => 'Garcia', 'gender' => 'female', 'email' => 'ana.garcia@test.com', 'birth_year' => 2001],
            ['first_name' => 'Carmen', 'last_name' => 'Torres', 'gender' => 'female', 'email' => 'carmen.torres@test.com', 'birth_year' => 2002],
            ['first_name' => 'Rosa', 'last_name' => 'Fernandez', 'gender' => 'female', 'email' => 'rosa.fernandez@test.com', 'birth_year' => 2003],
            ['first_name' => 'Luna', 'last_name' => 'Villanueva', 'gender' => 'female', 'email' => 'luna.villanueva@test.com', 'birth_year' => 2004],
            ['first_name' => 'Diana', 'last_name' => 'Ramirez', 'gender' => 'female', 'email' => 'diana.ramirez@test.com', 'birth_year' => 2005],
            ['first_name' => 'Patricia', 'last_name' => 'Castro', 'gender' => 'female', 'email' => 'patricia.castro@test.com', 'birth_year' => 2006],
            
            // Club 2 - Junior Male players (age < 18, birth_year >= 2007)
            ['first_name' => 'Miguel', 'last_name' => 'Ramos', 'gender' => 'male', 'email' => 'miguel.ramos@test.com', 'birth_year' => 2008],
            ['first_name' => 'Carlos', 'last_name' => 'Villanueva', 'gender' => 'male', 'email' => 'carlos.villanueva@test.com', 'birth_year' => 2009],
            ['first_name' => 'Diego', 'last_name' => 'Sanchez', 'gender' => 'male', 'email' => 'diego.sanchez@test.com', 'birth_year' => 2010],
            ['first_name' => 'Fernando', 'last_name' => 'Gonzalez', 'gender' => 'male', 'email' => 'fernando.gonzalez@test.com', 'birth_year' => 2011],
            ['first_name' => 'Ricardo', 'last_name' => 'Torres', 'gender' => 'male', 'email' => 'ricardo.torres@test.com', 'birth_year' => 2012],
            ['first_name' => 'Antonio', 'last_name' => 'Jimenez', 'gender' => 'male', 'email' => 'antonio.jimenez@test.com', 'birth_year' => 2013],
            ['first_name' => 'Roberto', 'last_name' => 'Silva', 'gender' => 'male', 'email' => 'roberto.silva@test.com', 'birth_year' => 2014],
            // Club 2 - Junior Female players
            ['first_name' => 'Sofia', 'last_name' => 'Cruz', 'gender' => 'female', 'email' => 'sofia.cruz@test.com', 'birth_year' => 2008],
            ['first_name' => 'Isabella', 'last_name' => 'Fernandez', 'gender' => 'female', 'email' => 'isabella.fernandez@test.com', 'birth_year' => 2009],
            ['first_name' => 'Elena', 'last_name' => 'Torres', 'gender' => 'female', 'email' => 'elena.torres@test.com', 'birth_year' => 2010],
            ['first_name' => 'Valentina', 'last_name' => 'Morales', 'gender' => 'female', 'email' => 'valentina.morales@test.com', 'birth_year' => 2011],
            ['first_name' => 'Camila', 'last_name' => 'Herrera', 'gender' => 'female', 'email' => 'camila.herrera@test.com', 'birth_year' => 2012],
            ['first_name' => 'Gabriela', 'last_name' => 'Mendoza', 'gender' => 'female', 'email' => 'gabriela.mendoza@test.com', 'birth_year' => 2013],
            ['first_name' => 'Natalia', 'last_name' => 'Rivera', 'gender' => 'female', 'email' => 'natalia.rivera@test.com', 'birth_year' => 2014],
        ];

        foreach ($playerData as $index => $data) {
            $player = User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'first_name' => $data['first_name'],
                'middle_name' => '',
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'player',
                'contact_number' => '09' . rand(100000000, 999999999),
                'gender' => $data['gender'],
                'birth_month' => rand(1, 12),
                'birth_day' => rand(1, 28),
                'birth_year' => $data['birth_year'],
                'height' => $data['gender'] === 'male' ? rand(165, 185) : rand(155, 175),
                'weight' => $data['gender'] === 'male' ? rand(60, 85) : rand(50, 70),
                'region' => 'NCR',
                'province' => 'Metro Manila',
                'city' => 'Manila',
                'school_status' => 'college_graduate',
                'school_name' => 'University of the Philippines',
                'badminton_history' => ['tournament', 'club_member'],
                'years_of_experience' => ['less_than_1', '1_2', '3_5', '6_10', 'more_than_10'][rand(0, 4)],
                'experience_level' => ['beginner', 'lower_intermediate', 'intermediate', 'upper_intermediate', 'advanced'][rand(0, 4)],
                'competitive_background' => ['school_competitions', 'local_tournaments', 'regional_tournaments', 'national_tournaments', 'none'][rand(0, 4)],
                'id_type' => ['philsys_id', 'drivers_license', 'umid_sss', 'philhealth', 'tin', 'passport', 'voters_id', 'postal_id'][rand(0, 7)],
                'biodata_completed' => true,
                'email_verified_at' => now(),
            ]);

            $players[] = $player;

            // Assign to club (14 players per club)
            $clubIndex = intval($index / 14);
            $skillLevels = ['A', 'B', 'C'];
            $skillLevel = $skillLevels[array_rand($skillLevels)];
            $provisionalElo = rand(1200, 1600);
            
            ClubPlayer::create([
                'club_id' => $clubs[$clubIndex]->id,
                'player_id' => $player->id,
                'status' => 'approved',
                'skill_level' => $skillLevel,
                'provisional_elo' => $provisionalElo,
                'is_provisional' => true,
            ]);

            // Create ELO ratings for all categories
            $categories = ['MS', 'WS', 'MD', 'WD', 'XD'];
            foreach ($categories as $category) {
                // Only create ELO for relevant categories
                if (($category === 'MS' || $category === 'MD' || $category === 'XD') && $data['gender'] === 'male') {
                    EloRating::create([
                        'player_id' => $player->id,
                        'category' => $category,
                        'current_rating' => $provisionalElo + rand(-50, 50),
                        'peak_rating' => $provisionalElo + rand(0, 100),
                        'matches_played' => rand(0, 10),
                    ]);
                }
                if (($category === 'WS' || $category === 'WD' || $category === 'XD') && $data['gender'] === 'female') {
                    EloRating::create([
                        'player_id' => $player->id,
                        'category' => $category,
                        'current_rating' => $provisionalElo + rand(-50, 50),
                        'peak_rating' => $provisionalElo + rand(0, 100),
                        'matches_played' => rand(0, 10),
                    ]);
                }
            }
        }

        $this->command->info('✓ Created 28 players (14 per club) with ELO ratings');

        // ============================================
        // CREATE ADDITIONAL PLAYERS WITHOUT CLUBS (for invitation testing)
        // ============================================
        $unaffiliatedPlayers = [];
        $unaffiliatedData = [
            ['first_name' => 'Carlos', 'last_name' => 'Rivera', 'gender' => 'male', 'email' => 'carlos.rivera@test.com', 'birth_year' => 1998],
            ['first_name' => 'Elena', 'last_name' => 'Martinez', 'gender' => 'female', 'email' => 'elena.martinez@test.com', 'birth_year' => 1999],
            ['first_name' => 'Roberto', 'last_name' => 'Gomez', 'gender' => 'male', 'email' => 'roberto.gomez@test.com', 'birth_year' => 1997],
            ['first_name' => 'Isabella', 'last_name' => 'Lopez', 'gender' => 'female', 'email' => 'isabella.lopez@test.com', 'birth_year' => 2000],
        ];

        foreach ($unaffiliatedData as $data) {
            $player = User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'first_name' => $data['first_name'],
                'middle_name' => '',
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'player',
                'contact_number' => '09' . rand(100000000, 999999999),
                'gender' => $data['gender'],
                'birth_month' => rand(1, 12),
                'birth_day' => rand(1, 28),
                'birth_year' => $data['birth_year'],
                'height' => $data['gender'] === 'male' ? rand(165, 185) : rand(155, 175),
                'weight' => $data['gender'] === 'male' ? rand(60, 85) : rand(50, 70),
                'region' => 'NCR',
                'province' => 'Metro Manila',
                'city' => 'Manila',
                'school_status' => 'college_student',
                'school_name' => 'Ateneo de Manila University',
                'badminton_history' => ['tournament', 'club_member', 'varsity'],
                'years_of_experience' => ['1_2', '3_5', '6_10'][rand(0, 2)],
                'experience_level' => ['intermediate', 'upper_intermediate', 'advanced'][rand(0, 2)],
                'competitive_background' => ['local_tournaments', 'regional_tournaments', 'national_tournaments'][rand(0, 2)],
                'id_type' => ['student_id', 'national_id', 'passport'][rand(0, 2)],
                'biodata_completed' => true,
                'email_verified_at' => now(),
            ]);

            $unaffiliatedPlayers[] = $player;

            // Create ELO ratings for unaffiliated players
            $categories = ['MS', 'WS', 'MD', 'WD', 'XD'];
            $baseElo = rand(1300, 1700);
            foreach ($categories as $category) {
                if (($category === 'MS' || $category === 'MD' || $category === 'XD') && $data['gender'] === 'male') {
                    EloRating::create([
                        'player_id' => $player->id,
                        'category' => $category,
                        'current_rating' => $baseElo + rand(-50, 50),
                        'peak_rating' => $baseElo + rand(0, 100),
                        'matches_played' => rand(0, 5),
                    ]);
                }
                if (($category === 'WS' || $category === 'WD' || $category === 'XD') && $data['gender'] === 'female') {
                    EloRating::create([
                        'player_id' => $player->id,
                        'category' => $category,
                        'current_rating' => $baseElo + rand(-50, 50),
                        'peak_rating' => $baseElo + rand(0, 100),
                        'matches_played' => rand(0, 5),
                    ]);
                }
            }
        }

        $this->command->info('✓ Created 4 unaffiliated players (for invitation testing)');

        // ============================================
        // CREATE CLUB INVITATIONS
        // ============================================
        // Club 1 invites 2 unaffiliated players
        if (count($unaffiliatedPlayers) >= 2) {
            // Invitation 1: Pending (player hasn't responded)
            $invitation1 = ClubPlayer::create([
                'club_id' => $clubs[0]->id,
                'player_id' => $unaffiliatedPlayers[0]->id,
                'status' => 'invited',
                'request_type' => 'invitation',
            ]);

            Notification::create([
                'user_id' => $unaffiliatedPlayers[0]->id,
                'type' => 'club_invitation',
                'title' => 'Club Invitation',
                'message' => "You have been invited to join {$clubs[0]->name}.",
                'data' => ['club_id' => $clubs[0]->id],
                'action_url' => '/clubs',
            ]);

            // Invitation 2: Pending (player hasn't responded)
            $invitation2 = ClubPlayer::create([
                'club_id' => $clubs[0]->id,
                'player_id' => $unaffiliatedPlayers[1]->id,
                'status' => 'invited',
                'request_type' => 'invitation',
            ]);

            Notification::create([
                'user_id' => $unaffiliatedPlayers[1]->id,
                'type' => 'club_invitation',
                'title' => 'Club Invitation',
                'message' => "You have been invited to join {$clubs[0]->name}.",
                'data' => ['club_id' => $clubs[0]->id],
                'action_url' => '/clubs',
            ]);

            // Club 2 invites 1 unaffiliated player
            if (count($unaffiliatedPlayers) >= 3) {
                $invitation3 = ClubPlayer::create([
                    'club_id' => $clubs[1]->id,
                    'player_id' => $unaffiliatedPlayers[2]->id,
                    'status' => 'invited',
                    'request_type' => 'invitation',
                ]);

                Notification::create([
                    'user_id' => $unaffiliatedPlayers[2]->id,
                    'type' => 'club_invitation',
                    'title' => 'Club Invitation',
                    'message' => "You have been invited to join {$clubs[1]->name}.",
                    'data' => ['club_id' => $clubs[1]->id],
                    'action_url' => '/clubs',
                ]);
            }
        }

        $this->command->info('✓ Created club invitations for testing');

        // Separate players by club and gender
        $club1Players = array_slice($players, 0, 14);
        $club2Players = array_slice($players, 14, 14);
        $club1Males = array_filter($club1Players, fn($p) => $p->gender === 'male');
        $club1Females = array_filter($club1Players, fn($p) => $p->gender === 'female');
        $club2Males = array_filter($club2Players, fn($p) => $p->gender === 'male');
        $club2Females = array_filter($club2Players, fn($p) => $p->gender === 'female');
        $club1Males = array_values($club1Males);
        $club1Females = array_values($club1Females);
        $club2Males = array_values($club2Males);
        $club2Females = array_values($club2Females);

        $matchService = app(MatchGenerationService::class);

        // ============================================
        // CLUB 1 TOURNAMENTS
        // ============================================

        // CLUB 1 - TOURNAMENT 1: PUBLISHED (for testing published status)
        $club1Published = Tournament::create([
            'name' => 'Metro Manila Singles Open 2025',
            'description' => 'Published tournament - ready for registration.',
            'organizer_id' => $managers[0]->id,
            'club_id' => $clubs[0]->id,
            'type' => 'mixed',
            'venue_name' => 'Metro Manila Sports Complex',
            'number_of_courts' => 4,
            'location' => 'Metro Manila Sports Complex, Manila',
            'contact_email' => 'info@metromanilabc.com',
            'contact_phone' => '02-123-4567',
            'tournament_fee' => 500,
            'registration_fee' => 500,
            'start_date' => $now->copy()->addDays(15),
            'end_date' => $now->copy()->addDays(17),
            'registration_deadline' => $now->copy()->addDays(10),
            'withdrawal_deadline' => $now->copy()->addDays(12),
            'status' => 'published',
            'is_dual_meet' => false,
            'bracket_type' => 'single_elimination',
        ]);

        $club1PublishedMS = TournamentCategory::create([
            'tournament_id' => $club1Published->id,
            'name' => "Men's Singles",
            'gender' => 'male',
            'max_participants' => 16,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $club1Published->start_date,
            'schedule_start_time' => '09:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        $club1PublishedWS = TournamentCategory::create([
            'tournament_id' => $club1Published->id,
            'name' => "Women's Singles",
            'gender' => 'female',
            'max_participants' => 16,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $club1Published->start_date,
            'schedule_start_time' => '10:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        $this->command->info('✓ Created Club 1 - Published Tournament');

        // CLUB 1 - TOURNAMENT 3: SINGLES (UPCOMING)
        $club1SinglesUpcoming = Tournament::create([
            'name' => 'Metro Manila Summer Open 2025',
            'description' => 'Upcoming singles tournament for testing registration, eligibility, and withdrawal.',
            'organizer_id' => $managers[0]->id,
            'club_id' => $clubs[0]->id,
            'type' => 'mixed',
            'venue_name' => 'Metro Manila Sports Complex',
            'number_of_courts' => 4,
            'location' => 'Metro Manila Sports Complex, Manila',
            'contact_email' => 'info@metromanilabc.com',
            'contact_phone' => '02-123-4567',
            'tournament_fee' => 500,
            'registration_fee' => 500,
            'start_date' => $now->copy()->addDays(20),
            'end_date' => $now->copy()->addDays(22),
            'registration_deadline' => $now->copy()->addDays(15),
            'withdrawal_deadline' => $now->copy()->addDays(17),
            'status' => 'upcoming',
            'is_dual_meet' => false,
            'bracket_type' => 'single_elimination',
        ]);

        $club1SinglesUpcomingMS = TournamentCategory::create([
            'tournament_id' => $club1SinglesUpcoming->id,
            'name' => "Men's Singles",
            'gender' => 'male',
            'max_participants' => 16,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $club1SinglesUpcoming->start_date,
            'schedule_start_time' => '09:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        $club1SinglesUpcomingWS = TournamentCategory::create([
            'tournament_id' => $club1SinglesUpcoming->id,
            'name' => "Women's Singles",
            'gender' => 'female',
            'max_participants' => 16,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $club1SinglesUpcoming->start_date,
            'schedule_start_time' => '10:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        $club1SinglesUpcomingMS = TournamentCategory::create([
            'tournament_id' => $club1SinglesUpcoming->id,
            'name' => "Men's Singles",
            'gender' => 'male',
            'max_participants' => 16,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $club1SinglesUpcoming->start_date,
            'schedule_start_time' => '09:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        $club1SinglesUpcomingWS = TournamentCategory::create([
            'tournament_id' => $club1SinglesUpcoming->id,
            'name' => "Women's Singles",
            'gender' => 'female',
            'max_participants' => 16,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $club1SinglesUpcoming->start_date,
            'schedule_start_time' => '10:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        // Register players with different statuses for testing
        $allMales = array_merge($club1Males, $club2Males);
        $allFemales = array_merge($club1Females, $club2Females);
        
        // Men's Singles - mix of statuses
        TournamentRegistration::create([
            'tournament_id' => $club1SinglesUpcoming->id,
            'category_id' => $club1SinglesUpcomingMS->id,
            'player_id' => $allMales[0]->id,
            'status' => 'approved',
        ]);
        TournamentRegistration::create([
            'tournament_id' => $club1SinglesUpcoming->id,
            'category_id' => $club1SinglesUpcomingMS->id,
            'player_id' => $allMales[1]->id,
            'status' => 'awaiting_payment',
        ]);
        TournamentRegistration::create([
            'tournament_id' => $club1SinglesUpcoming->id,
            'category_id' => $club1SinglesUpcomingMS->id,
            'player_id' => $allMales[2]->id,
            'status' => 'pending',
        ]);
        TournamentRegistration::create([
            'tournament_id' => $club1SinglesUpcoming->id,
            'category_id' => $club1SinglesUpcomingMS->id,
            'player_id' => $allMales[3]->id,
            'status' => 'rejected',
        ]);

        // Women's Singles
        TournamentRegistration::create([
            'tournament_id' => $club1SinglesUpcoming->id,
            'category_id' => $club1SinglesUpcomingWS->id,
            'player_id' => $allFemales[0]->id,
            'status' => 'approved',
        ]);
        TournamentRegistration::create([
            'tournament_id' => $club1SinglesUpcoming->id,
            'category_id' => $club1SinglesUpcomingWS->id,
            'player_id' => $allFemales[1]->id,
            'status' => 'awaiting_payment',
        ]);

        // Create withdrawal request for testing
        $withdrawalReg = TournamentRegistration::create([
            'tournament_id' => $club1SinglesUpcoming->id,
            'category_id' => $club1SinglesUpcomingMS->id,
            'player_id' => $allMales[4]->id,
            'status' => 'approved', // Status remains approved, withdrawal tracked separately
        ]);
        WithdrawalRequest::create([
            'tournament_registration_id' => $withdrawalReg->id,
            'reason' => 'Schedule conflict - cannot attend due to work commitment',
            'status' => 'pending',
            'refund_status' => 'pending',
        ]);

        $this->command->info('✓ Created Club 1 - Singles Tournament (Upcoming)');

        // CLUB 1 - TOURNAMENT 2: DOUBLES (ONGOING) - Main testing tournament
        $club1DoublesOngoing = Tournament::create([
            'name' => 'Metro Manila Doubles Championship 2025',
            'description' => 'Ongoing doubles tournament with matches scheduled at 11:30 PM today.',
            'organizer_id' => $managers[0]->id,
            'club_id' => $clubs[0]->id,
            'type' => 'mixed',
            'venue_name' => 'Metro Manila Sports Complex',
            'number_of_courts' => 4,
            'location' => 'Metro Manila Sports Complex, Manila',
            'contact_email' => 'info@metromanilabc.com',
            'contact_phone' => '02-123-4567',
            'tournament_fee' => 500,
            'registration_fee' => 500,
            'start_date' => $now->copy()->subDays(1),
            'end_date' => $now->copy()->addDays(2),
            'registration_deadline' => $now->copy()->subDays(7),
            'withdrawal_deadline' => $now->copy()->subDays(5),
            'status' => 'ongoing',
            'is_dual_meet' => false,
            'bracket_type' => 'single_elimination',
        ]);

        $club1DoublesOngoingMD = TournamentCategory::create([
            'tournament_id' => $club1DoublesOngoing->id,
            'name' => "Men's Doubles",
            'gender' => 'male',
            'max_participants' => 12,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $club1DoublesOngoing->start_date,
            'schedule_start_time' => '09:00:00',
            'match_duration_minutes' => 60,
            'break_between_matches_minutes' => 5,
        ]);

        // Register men's doubles teams for Round of 12 (need 12 players = 6 teams)
        // We have 14 male players total, so we'll use 12 of them (6 teams = 12 players)
        $mdRegistrations = [];
        $maleCount = count($allMales);
        $teamCount = 6; // Use 6 teams (12 players) for Round of 12
        for ($i = 0; $i < $teamCount; $i++) {
            $p1 = $allMales[$i * 2];
            $p2 = $allMales[$i * 2 + 1];
            $mdRegistrations[] = TournamentRegistration::create([
                'tournament_id' => $club1DoublesOngoing->id,
                'category_id' => $club1DoublesOngoingMD->id,
                'player_id' => $p1->id,
                'partner_id' => $p2->id,
                'status' => 'approved',
            ]);
            TournamentRegistration::create([
                'tournament_id' => $club1DoublesOngoing->id,
                'category_id' => $club1DoublesOngoingMD->id,
                'player_id' => $p2->id,
                'partner_id' => $p1->id,
                'status' => 'approved',
            ]);
        }

        // Generate matches
        $matchService->generateMatches($club1DoublesOngoing, 'single_elimination');
        $matches = TournamentMatch::where('tournament_id', $club1DoublesOngoing->id)
            ->where('tournament_category_id', $club1DoublesOngoingMD->id)
            ->orderBy('round')
            ->orderBy('match_number')
            ->get();

        // Set up match states: Round 1 completed, Quarterfinals mix, Semifinals scheduled
        $round1Matches = $matches->filter(fn($m) => str_contains($m->round, 'Round 1'));
        $qfMatches = $matches->filter(fn($m) => str_contains($m->round, 'Quarterfinal'));
        $sfMatches = $matches->filter(fn($m) => str_contains($m->round, 'Semifinal'));
        $finalMatches = $matches->filter(fn($m) => str_contains($m->round, 'Final'));

        // Round 1: All completed yesterday
        // For doubles, track both player and partner IDs
        $round1Winners = [];
        $round1WinnerPartners = [];
        foreach ($round1Matches as $index => $match) {
            if ($match->player2_id === null) {
                // Bye
                $match->update([
                    'status' => 'completed',
                    'scheduled_date' => $now->copy()->subDays(1),
                    'scheduled_time' => $now->copy()->subDays(1)->setTime(9, 0)->addHours($index),
                    'court_number' => ($index % 4) + 1,
                    'winner_id' => $match->player1_id,
                    'winner_partner_id' => $match->player1_partner_id,
                ]);
                $round1Winners[] = $match->player1_id;
                $round1WinnerPartners[] = $match->player1_partner_id;
            } else {
                $match->update([
                    'status' => 'completed',
                    'scheduled_date' => $now->copy()->subDays(1),
                    'scheduled_time' => $now->copy()->subDays(1)->setTime(9, 0)->addHours($index),
                    'court_number' => ($index % 4) + 1,
                    'winner_id' => $match->player1_id,
                    'winner_partner_id' => $match->player1_partner_id,
                ]);
                MatchResult::create([
                    'match_id' => $match->id,
                    'player1_set1_score' => 21,
                    'player2_set1_score' => 15,
                    'player1_set2_score' => 21,
                    'player2_set2_score' => 18,
                    'winner_id' => $match->player1_id,
                    'winner_partner_id' => $match->player1_partner_id,
                ]);
                $round1Winners[] = $match->player1_id;
                $round1WinnerPartners[] = $match->player1_partner_id;
            }
        }

        // Quarterfinals: Mix of completed, ongoing (at 11:30 PM), and scheduled
        // For doubles, preserve partner IDs
        $today1130PM = $now->copy()->setTime(23, 30);
        foreach ($qfMatches as $index => $match) {
            if (isset($round1Winners[$index * 2])) {
                $match->update([
                    'player1_id' => $round1Winners[$index * 2],
                    'player1_partner_id' => $round1WinnerPartners[$index * 2] ?? null,
                    'player2_id' => $round1Winners[$index * 2 + 1] ?? null,
                    'player2_partner_id' => isset($round1Winners[$index * 2 + 1]) ? ($round1WinnerPartners[$index * 2 + 1] ?? null) : null,
                ]);
            }
            
            if ($match->player2_id === null) {
                // Bye
                $match->update([
                    'status' => 'completed',
                    'scheduled_date' => $now->copy(),
                    'scheduled_time' => $now->copy()->setTime(10, 0),
                    'court_number' => 1,
                    'winner_id' => $match->player1_id,
                    'winner_partner_id' => $match->player1_partner_id,
                ]);
            } elseif ($index === 0) {
                // QF1: Completed earlier today
                $match->update([
                    'status' => 'completed',
                    'scheduled_date' => $now->copy(),
                    'scheduled_time' => $now->copy()->setTime(10, 0),
                    'court_number' => 1,
                    'winner_id' => $match->player1_id,
                    'winner_partner_id' => $match->player1_partner_id,
                ]);
                    MatchResult::create([
                        'match_id' => $match->id,
                        'player1_set1_score' => 21,
                        'player2_set1_score' => 19,
                        'player1_set2_score' => 21,
                        'player2_set2_score' => 17,
                        'winner_id' => $match->player1_id,
                        'winner_partner_id' => $match->player1_partner_id,
                    ]);
            } elseif ($index === 1) {
                // QF2: ONGOING at 11:30 PM (happening now)
                $match->update([
                    'status' => 'ongoing',
                    'scheduled_date' => $today1130PM->copy(),
                    'scheduled_time' => $today1130PM->copy(),
                    'court_number' => 2,
                ]);
            } elseif ($index === 2) {
                // QF3: RESCHEDULED (for testing rescheduling feature)
                $match->update([
                    'status' => 'scheduled',
                    'scheduled_date' => $now->copy()->addDay(),
                    'scheduled_time' => $now->copy()->addDay()->setTime(9, 0),
                    'court_number' => 3,
                    'reschedule_count' => 1, // Mark as rescheduled
                ]);

                // Create notification for rescheduled match
                if ($match->player1_id) {
                    Notification::create([
                        'user_id' => $match->player1_id,
                        'type' => 'match_rescheduled',
                        'title' => 'Match Rescheduled',
                        'message' => "Your match in {$club1DoublesOngoing->name} has been rescheduled to " . $now->copy()->addDay()->format('M d, Y') . " at " . $now->copy()->addDay()->setTime(9, 0)->format('h:i A') . ".",
                        'data' => ['match_id' => $match->id, 'tournament_id' => $club1DoublesOngoing->id],
                        'action_url' => '/player/matches/' . $match->id,
                    ]);
                }
                if ($match->player2_id) {
                    Notification::create([
                        'user_id' => $match->player2_id,
                        'type' => 'match_rescheduled',
                        'title' => 'Match Rescheduled',
                        'message' => "Your match in {$club1DoublesOngoing->name} has been rescheduled to " . $now->copy()->addDay()->format('M d, Y') . " at " . $now->copy()->addDay()->setTime(9, 0)->format('h:i A') . ".",
                        'data' => ['match_id' => $match->id, 'tournament_id' => $club1DoublesOngoing->id],
                        'action_url' => '/player/matches/' . $match->id,
                    ]);
                }
                // Also notify partners if doubles
                if ($match->player1_partner_id) {
                    Notification::create([
                        'user_id' => $match->player1_partner_id,
                        'type' => 'match_rescheduled',
                        'title' => 'Match Rescheduled',
                        'message' => "Your match in {$club1DoublesOngoing->name} has been rescheduled to " . $now->copy()->addDay()->format('M d, Y') . " at " . $now->copy()->addDay()->setTime(9, 0)->format('h:i A') . ".",
                        'data' => ['match_id' => $match->id, 'tournament_id' => $club1DoublesOngoing->id],
                        'action_url' => '/player/matches/' . $match->id,
                    ]);
                }
                if ($match->player2_partner_id) {
                    Notification::create([
                        'user_id' => $match->player2_partner_id,
                        'type' => 'match_rescheduled',
                        'title' => 'Match Rescheduled',
                        'message' => "Your match in {$club1DoublesOngoing->name} has been rescheduled to " . $now->copy()->addDay()->format('M d, Y') . " at " . $now->copy()->addDay()->setTime(9, 0)->format('h:i A') . ".",
                        'data' => ['match_id' => $match->id, 'tournament_id' => $club1DoublesOngoing->id],
                        'action_url' => '/player/matches/' . $match->id,
                    ]);
                }
            } else {
                // QF4+: Scheduled for later
                $match->update([
                    'status' => 'scheduled',
                    'scheduled_date' => $now->copy()->addDay(),
                    'scheduled_time' => $now->copy()->addDay()->setTime(9, 0),
                    'court_number' => ($index % 4) + 1,
                ]);
            }
        }

        // Semifinals and Finals: Scheduled
        foreach ($sfMatches as $match) {
            $match->update([
                'status' => 'scheduled',
                'scheduled_date' => $now->copy()->addDay(),
                'scheduled_time' => $now->copy()->addDay()->setTime(14, 0),
                'court_number' => 1,
            ]);
        }
        foreach ($finalMatches as $match) {
            $match->update([
                'status' => 'scheduled',
                'scheduled_date' => $now->copy()->addDays(2),
                'scheduled_time' => $now->copy()->addDays(2)->setTime(16, 0),
                'court_number' => 1,
            ]);
        }

        $this->command->info('✓ Created Club 1 - Doubles Tournament (Ongoing) with matches at 11:30 PM');

        // CLUB 1 - TOURNAMENT 3: ALL CATEGORIES (COMPLETED)
        $club1AllCompleted = Tournament::create([
            'name' => 'Metro Manila Grand Championship 2024',
            'description' => 'Completed tournament with all categories.',
            'organizer_id' => $managers[0]->id,
            'club_id' => $clubs[0]->id,
            'type' => 'mixed',
            'venue_name' => 'Metro Manila Sports Complex',
            'number_of_courts' => 4,
            'location' => 'Metro Manila Sports Complex, Manila',
            'contact_email' => 'info@metromanilabc.com',
            'contact_phone' => '02-123-4567',
            'tournament_fee' => 500,
            'registration_fee' => 500,
            'start_date' => $now->copy()->subDays(30),
            'end_date' => $now->copy()->subDays(27),
            'registration_deadline' => $now->copy()->subDays(35),
            'withdrawal_deadline' => $now->copy()->subDays(32),
            'status' => 'completed',
            'is_dual_meet' => false,
            'bracket_type' => 'single_elimination',
        ]);

        $categories = [
            ['name' => "Men's Singles", 'gender' => 'male', 'type' => 'MS'],
            ['name' => "Women's Singles", 'gender' => 'female', 'type' => 'WS'],
            ['name' => "Men's Doubles", 'gender' => 'male', 'type' => 'MD'],
            ['name' => "Women's Doubles", 'gender' => 'female', 'type' => 'WD'],
            ['name' => "Mixed Doubles", 'gender' => 'mixed', 'type' => 'XD'],
        ];

        foreach ($categories as $catData) {
            $category = TournamentCategory::create([
                'tournament_id' => $club1AllCompleted->id,
                'name' => $catData['name'],
                'gender' => $catData['gender'],
                'max_participants' => 12,
                'skill_level' => 'Open',
                'min_age' => null,
                'max_age' => null,
                'schedule_start_date' => $club1AllCompleted->start_date,
                'schedule_start_time' => '09:00:00',
                'match_duration_minutes' => in_array($catData['type'], ['MS', 'WS']) ? 45 : 60,
                'break_between_matches_minutes' => 5,
            ]);

            // Register players based on category type
            if ($catData['type'] === 'MS') {
                for ($i = 0; $i < 6; $i++) {
                    TournamentRegistration::create([
                        'tournament_id' => $club1AllCompleted->id,
                        'category_id' => $category->id,
                        'player_id' => $allMales[$i]->id,
                        'status' => 'approved',
                    ]);
                }
            } elseif ($catData['type'] === 'WS') {
                for ($i = 0; $i < 6; $i++) {
                    TournamentRegistration::create([
                        'tournament_id' => $club1AllCompleted->id,
                        'category_id' => $category->id,
                        'player_id' => $allFemales[$i]->id,
                        'status' => 'approved',
                    ]);
                }
            } elseif ($catData['type'] === 'MD') {
                for ($i = 0; $i < 3; $i++) {
                    $p1 = $allMales[$i * 2];
                    $p2 = $allMales[$i * 2 + 1];
                    TournamentRegistration::create([
                        'tournament_id' => $club1AllCompleted->id,
                        'category_id' => $category->id,
                        'player_id' => $p1->id,
                        'partner_id' => $p2->id,
                        'status' => 'approved',
                    ]);
                    TournamentRegistration::create([
                        'tournament_id' => $club1AllCompleted->id,
                        'category_id' => $category->id,
                        'player_id' => $p2->id,
                        'partner_id' => $p1->id,
                        'status' => 'approved',
                    ]);
                }
            } elseif ($catData['type'] === 'WD') {
                for ($i = 0; $i < 3; $i++) {
                    $p1 = $allFemales[$i * 2];
                    $p2 = $allFemales[$i * 2 + 1];
                    TournamentRegistration::create([
                        'tournament_id' => $club1AllCompleted->id,
                        'category_id' => $category->id,
                        'player_id' => $p1->id,
                        'partner_id' => $p2->id,
                        'status' => 'approved',
                    ]);
                    TournamentRegistration::create([
                        'tournament_id' => $club1AllCompleted->id,
                        'category_id' => $category->id,
                        'player_id' => $p2->id,
                        'partner_id' => $p1->id,
                        'status' => 'approved',
                    ]);
                }
            } elseif ($catData['type'] === 'XD') {
                for ($i = 0; $i < 3; $i++) {
                    $p1 = $allMales[$i];
                    $p2 = $allFemales[$i];
                    TournamentRegistration::create([
                        'tournament_id' => $club1AllCompleted->id,
                        'category_id' => $category->id,
                        'player_id' => $p1->id,
                        'partner_id' => $p2->id,
                        'status' => 'approved',
                    ]);
                    TournamentRegistration::create([
                        'tournament_id' => $club1AllCompleted->id,
                        'category_id' => $category->id,
                        'player_id' => $p2->id,
                        'partner_id' => $p1->id,
                        'status' => 'approved',
                    ]);
                }
            }

            // Generate matches and complete them with proper bracket progression
            $matchService->generateMatches($club1AllCompleted, 'single_elimination');
            $catMatches = TournamentMatch::where('tournament_id', $club1AllCompleted->id)
                ->where('tournament_category_id', $category->id)
                ->orderByRaw("CASE 
                    WHEN round LIKE 'Round 1%' THEN 1
                    WHEN round LIKE 'Round of%' THEN 2
                    WHEN round LIKE 'Quarterfinal%' THEN 3
                    WHEN round LIKE 'Semifinal%' THEN 4
                    WHEN round LIKE 'Final%' THEN 5
                    ELSE 6
                END")
                ->orderBy('match_number')
                ->get();

            // Group matches by round
            $matchesByRound = [];
            foreach ($catMatches as $match) {
                $roundName = $match->round;
                if (!isset($matchesByRound[$roundName])) {
                    $matchesByRound[$roundName] = [];
                }
                $matchesByRound[$roundName][] = $match;
            }

            // Process matches round by round, advancing winners properly
            $eloService = app(EloRatingService::class);
            $categoryMap = [
                "Men's Singles" => 'MS',
                "Women's Singles" => 'WS',
                "Men's Doubles" => 'MD',
                "Women's Doubles" => 'WD',
                "Mixed Doubles" => 'XD',
            ];
            $eloCategory = $categoryMap[$catData['name']] ?? 'MS';
            
            foreach ($matchesByRound as $roundName => $roundMatches) {
                foreach ($roundMatches as $index => $match) {
                    if ($match->player1_id && $match->player2_id) {
                        // Both players present - determine winner (alternate for variety)
                        $player1Won = ($index % 2 === 0);
                        $winnerId = $player1Won ? $match->player1_id : $match->player2_id;
                        $winnerPartnerId = $player1Won ? $match->player1_partner_id : $match->player2_partner_id;
                        
                        // Create match result
                        MatchResult::create([
                            'match_id' => $match->id,
                            'player1_set1_score' => $player1Won ? 21 : 15,
                            'player2_set1_score' => $player1Won ? 15 : 21,
                            'player1_set2_score' => $player1Won ? 21 : 18,
                            'player2_set2_score' => $player1Won ? 18 : 21,
                            'winner_id' => $winnerId,
                            'winner_partner_id' => $winnerPartnerId,
                        ]);
                        
                        // Update match
                        $match->update([
                            'status' => 'completed',
                            'scheduled_date' => $club1AllCompleted->start_date,
                            'scheduled_time' => Carbon::parse($club1AllCompleted->start_date)->setTime(9, 0)->addHours($index),
                            'court_number' => ($index % 4) + 1,
                            'winner_id' => $winnerId,
                            'winner_partner_id' => $winnerPartnerId,
                        ]);
                        
                        // Calculate ELO ratings
                        if ($catData['type'] === 'MS' || $catData['type'] === 'WS') {
                            $player1 = User::find($match->player1_id);
                            $player2 = User::find($match->player2_id);
                            if ($player1 && $player2) {
                                $eloService->calculateMatchRatings($player1, $player2, $player1Won, $eloCategory);
                            }
                        } else {
                            // Doubles
                            $p1 = User::find($match->player1_id);
                            $p2 = User::find($match->player1_partner_id);
                            $p3 = User::find($match->player2_id);
                            $p4 = User::find($match->player2_partner_id);
                            if ($p1 && $p2 && $p3 && $p4) {
                                $eloService->calculateDoublesMatchRatings($p1, $p2, $p3, $p4, $player1Won, $eloCategory);
                            }
                        }
                        
                        // Advance winner to next round
                        $matchService->advanceWinner($match);
                    } elseif ($match->player1_id) {
                        // Bye - advance automatically
                        $match->update([
                            'status' => 'completed',
                            'scheduled_date' => $club1AllCompleted->start_date,
                            'scheduled_time' => Carbon::parse($club1AllCompleted->start_date)->setTime(9, 0)->addHours($index),
                            'court_number' => ($index % 4) + 1,
                            'winner_id' => $match->player1_id,
                            'winner_partner_id' => $match->player1_partner_id,
                        ]);
                        $matchService->advanceWinner($match);
                    }
                }
            }
        }

        $this->command->info('✓ Created Club 1 - All Categories Tournament (Completed)');

        // ============================================
        // CLUB 2 TOURNAMENTS
        // ============================================

        // CLUB 2 - TOURNAMENT 1: SINGLES (ONGOING)
        $club2SinglesOngoing = Tournament::create([
            'name' => 'Cebu Singles Championship 2025',
            'description' => 'Ongoing singles tournament.',
            'organizer_id' => $managers[1]->id,
            'club_id' => $clubs[1]->id,
            'type' => 'mixed',
            'venue_name' => 'Cebu Sports Complex',
            'number_of_courts' => 4,
            'location' => 'Cebu Sports Complex, Cebu City',
            'contact_email' => 'info@cebuelitebc.com',
            'contact_phone' => '032-555-6789',
            'tournament_fee' => 500,
            'registration_fee' => 500,
            'start_date' => $now->copy()->subDays(1),
            'end_date' => $now->copy()->addDays(2),
            'registration_deadline' => $now->copy()->subDays(7),
            'withdrawal_deadline' => $now->copy()->subDays(5),
            'status' => 'ongoing',
            'is_dual_meet' => false,
            'bracket_type' => 'single_elimination',
        ]);

        $club2SinglesOngoingMS = TournamentCategory::create([
            'tournament_id' => $club2SinglesOngoing->id,
            'name' => "Men's Singles",
            'gender' => 'male',
            'max_participants' => 12,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $club2SinglesOngoing->start_date,
            'schedule_start_time' => '09:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        // Register 12 players for Round of 12
        for ($i = 0; $i < 12 && $i < count($allMales); $i++) {
            TournamentRegistration::create([
                'tournament_id' => $club2SinglesOngoing->id,
                'category_id' => $club2SinglesOngoingMS->id,
                'player_id' => $allMales[$i]->id,
                'status' => 'approved',
            ]);
        }

        $matchService->generateMatches($club2SinglesOngoing, 'single_elimination');
        
        // Set up ongoing tournament with matches that need result input
        $ongoingMatches = TournamentMatch::where('tournament_id', $club2SinglesOngoing->id)
            ->where('tournament_category_id', $club2SinglesOngoingMS->id)
            ->orderBy('match_number')
            ->get();
        
        // Complete Round 1 matches
        $round1Matches = $ongoingMatches->filter(fn($m) => str_contains($m->round, 'Round 1'));
        foreach ($round1Matches as $index => $match) {
            if ($match->player1_id && $match->player2_id) {
                $winnerId = ($index % 2 === 0) ? $match->player1_id : $match->player2_id;
                $player1Won = ($winnerId === $match->player1_id);
                
                MatchResult::create([
                    'match_id' => $match->id,
                    'player1_set1_score' => $player1Won ? 21 : 15,
                    'player2_set1_score' => $player1Won ? 15 : 21,
                    'player1_set2_score' => $player1Won ? 21 : 18,
                    'player2_set2_score' => $player1Won ? 18 : 21,
                    'winner_id' => $winnerId,
                    'winner_partner_id' => $winnerPartnerId,
                ]);
                
                $match->update([
                    'status' => 'completed',
                    'scheduled_date' => $club2SinglesOngoing->start_date,
                    'scheduled_time' => Carbon::parse($club2SinglesOngoing->start_date)->setTime(9, 0)->addHours($index),
                    'court_number' => ($index % 4) + 1,
                    'winner_id' => $winnerId,
                ]);
                
                $matchService->advanceWinner($match);
            }
        }
        
        // Leave at least 2 Quarterfinal matches without results (for testing result input)
        $qfMatches = $ongoingMatches->filter(fn($m) => str_contains($m->round, 'Quarterfinal'));
        $qfCount = 0;
        foreach ($qfMatches as $index => $match) {
            if ($qfCount < 2) {
                // Leave these without results for testing
                $match->update([
                    'status' => 'scheduled',
                    'scheduled_date' => $now->copy()->addDay(),
                    'scheduled_time' => $now->copy()->addDay()->setTime(10, 0)->addHours($index),
                    'court_number' => ($index % 4) + 1,
                ]);
                $qfCount++;
            } else {
                // Complete the rest
                if ($match->player1_id && $match->player2_id) {
                    $winnerId = $match->player1_id;
                    MatchResult::create([
                        'match_id' => $match->id,
                        'player1_set1_score' => 21,
                        'player2_set1_score' => 15,
                        'player1_set2_score' => 21,
                        'player2_set2_score' => 18,
                        'winner_id' => $winnerId,
                        'winner_partner_id' => $winnerPartnerId ?? null,
                    ]);
                    $match->update([
                        'status' => 'completed',
                        'scheduled_date' => $now->copy(),
                        'scheduled_time' => $now->copy()->setTime(10, 0)->addHours($index),
                        'court_number' => ($index % 4) + 1,
                        'winner_id' => $winnerId,
                    ]);
                    $matchService->advanceWinner($match);
                }
            }
        }
        
        $this->command->info('✓ Created Club 2 - Singles Tournament (Ongoing) with 2 matches needing result input');

        // CLUB 2 - TOURNAMENT 2: DOUBLES (COMPLETED)
        $club2DoublesCompleted = Tournament::create([
            'name' => 'Cebu Doubles Championship 2024',
            'description' => 'Completed doubles tournament.',
            'organizer_id' => $managers[1]->id,
            'club_id' => $clubs[1]->id,
            'type' => 'mixed',
            'venue_name' => 'Cebu Sports Complex',
            'number_of_courts' => 4,
            'location' => 'Cebu Sports Complex, Cebu City',
            'contact_email' => 'info@cebuelitebc.com',
            'contact_phone' => '032-555-6789',
            'tournament_fee' => 500,
            'registration_fee' => 500,
            'start_date' => $now->copy()->subDays(30),
            'end_date' => $now->copy()->subDays(27),
            'registration_deadline' => $now->copy()->subDays(35),
            'withdrawal_deadline' => $now->copy()->subDays(32),
            'status' => 'completed',
            'is_dual_meet' => false,
            'bracket_type' => 'single_elimination',
        ]);

        $club2DoublesCompletedMD = TournamentCategory::create([
            'tournament_id' => $club2DoublesCompleted->id,
            'name' => "Men's Doubles",
            'gender' => 'male',
            'max_participants' => 12,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $club2DoublesCompleted->start_date,
            'schedule_start_time' => '09:00:00',
            'match_duration_minutes' => 60,
            'break_between_matches_minutes' => 5,
        ]);

        $club2DoublesCompletedWD = TournamentCategory::create([
            'tournament_id' => $club2DoublesCompleted->id,
            'name' => "Women's Doubles",
            'gender' => 'female',
            'max_participants' => 12,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $club2DoublesCompleted->start_date,
            'schedule_start_time' => '10:00:00',
            'match_duration_minutes' => 60,
            'break_between_matches_minutes' => 5,
        ]);

        $club2DoublesCompletedXD = TournamentCategory::create([
            'tournament_id' => $club2DoublesCompleted->id,
            'name' => "Mixed Doubles",
            'gender' => 'mixed',
            'max_participants' => 12,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $club2DoublesCompleted->start_date,
            'schedule_start_time' => '11:00:00',
            'match_duration_minutes' => 60,
            'break_between_matches_minutes' => 5,
        ]);

        // Register teams and generate completed matches
        foreach ([$club2DoublesCompletedMD, $club2DoublesCompletedWD, $club2DoublesCompletedXD] as $category) {
            $isMixed = str_contains($category->name, 'Mixed');
            $isWomen = str_contains($category->name, "Women's");
            
            // Use available players, starting from index 5 (Club 2 players)
            $availableMales = array_slice($allMales, 5);
            $availableFemales = array_slice($allFemales, 5);
            $teamCount = min(3, intval(count($availableMales) / 2), intval(count($availableFemales) / 2));
            
            for ($i = 0; $i < $teamCount; $i++) {
                if ($isMixed) {
                    // Mixed doubles: one male, one female
                    if ($i < count($availableMales) && $i < count($availableFemales)) {
                        $p1 = $availableMales[$i];
                        $p2 = $availableFemales[$i];
                    } else {
                        continue; // Skip if not enough players
                    }
                } elseif ($isWomen) {
                    // Women's doubles: two females
                    if ($i * 2 + 1 < count($availableFemales)) {
                        $p1 = $availableFemales[$i * 2];
                        $p2 = $availableFemales[$i * 2 + 1];
                    } else {
                        continue; // Skip if not enough players
                    }
                } else {
                    // Men's doubles: two males
                    if ($i * 2 + 1 < count($availableMales)) {
                        $p1 = $availableMales[$i * 2];
                        $p2 = $availableMales[$i * 2 + 1];
                    } else {
                        continue; // Skip if not enough players
                    }
                }
                
                // Only create if we have two different players
                if ($p1->id !== $p2->id) {
                    TournamentRegistration::create([
                        'tournament_id' => $club2DoublesCompleted->id,
                        'category_id' => $category->id,
                        'player_id' => $p1->id,
                        'partner_id' => $p2->id,
                        'status' => 'approved',
                    ]);
                    TournamentRegistration::create([
                        'tournament_id' => $club2DoublesCompleted->id,
                        'category_id' => $category->id,
                        'player_id' => $p2->id,
                        'partner_id' => $p1->id,
                        'status' => 'approved',
                    ]);
                }
            }

            $matchService->generateMatches($club2DoublesCompleted, 'single_elimination');
            $catMatches = TournamentMatch::where('tournament_id', $club2DoublesCompleted->id)
                ->where('tournament_category_id', $category->id)
                ->orderByRaw("CASE 
                    WHEN round LIKE 'Round 1%' THEN 1
                    WHEN round LIKE 'Round of%' THEN 2
                    WHEN round LIKE 'Quarterfinal%' THEN 3
                    WHEN round LIKE 'Semifinal%' THEN 4
                    WHEN round LIKE 'Final%' THEN 5
                    ELSE 6
                END")
                ->orderBy('match_number')
                ->get();

            // Group by round and process properly
            $matchesByRound = [];
            foreach ($catMatches as $match) {
                $roundName = $match->round;
                if (!isset($matchesByRound[$roundName])) {
                    $matchesByRound[$roundName] = [];
                }
                $matchesByRound[$roundName][] = $match;
            }

            $eloService = app(EloRatingService::class);
            $categoryMap = [
                "Men's Doubles" => 'MD',
                "Women's Doubles" => 'WD',
                "Mixed Doubles" => 'XD',
            ];
            $eloCategory = $categoryMap[$category->name] ?? 'MD';

            foreach ($matchesByRound as $roundName => $roundMatches) {
                foreach ($roundMatches as $index => $match) {
                    if ($match->player1_id && $match->player2_id) {
                        $player1Won = ($index % 2 === 0);
                        $winnerId = $player1Won ? $match->player1_id : $match->player2_id;
                        $winnerPartnerId = $player1Won ? $match->player1_partner_id : $match->player2_partner_id;
                        
                        MatchResult::create([
                            'match_id' => $match->id,
                            'player1_set1_score' => $player1Won ? 21 : 15,
                            'player2_set1_score' => $player1Won ? 15 : 21,
                            'player1_set2_score' => $player1Won ? 21 : 18,
                            'player2_set2_score' => $player1Won ? 18 : 21,
                            'winner_id' => $winnerId,
                        ]);
                        
                        $match->update([
                            'status' => 'completed',
                            'scheduled_date' => $club2DoublesCompleted->start_date,
                            'scheduled_time' => Carbon::parse($club2DoublesCompleted->start_date)->setTime(9, 0)->addHours($index),
                            'court_number' => ($index % 4) + 1,
                            'winner_id' => $winnerId,
                            'winner_partner_id' => $winnerPartnerId,
                        ]);
                        
                        // Calculate ELO for doubles
                        $p1 = User::find($match->player1_id);
                        $p2 = User::find($match->player1_partner_id);
                        $p3 = User::find($match->player2_id);
                        $p4 = User::find($match->player2_partner_id);
                        if ($p1 && $p2 && $p3 && $p4) {
                            $eloService->calculateDoublesMatchRatings($p1, $p2, $p3, $p4, $player1Won, $eloCategory);
                        }
                        
                        $matchService->advanceWinner($match);
                    } elseif ($match->player1_id) {
                        $match->update([
                            'status' => 'completed',
                            'scheduled_date' => $club2DoublesCompleted->start_date,
                            'scheduled_time' => Carbon::parse($club2DoublesCompleted->start_date)->setTime(9, 0)->addHours($index),
                            'court_number' => ($index % 4) + 1,
                            'winner_id' => $match->player1_id,
                            'winner_partner_id' => $match->player1_partner_id,
                        ]);
                        $matchService->advanceWinner($match);
                    }
                }
            }
        }

        $this->command->info('✓ Created Club 2 - Doubles Tournament (Completed)');

        // CLUB 2 - TOURNAMENT 3: ALL CATEGORIES (UPCOMING)
        $club2AllUpcoming = Tournament::create([
            'name' => 'Cebu Grand Open 2025',
            'description' => 'Upcoming tournament with all categories.',
            'organizer_id' => $managers[1]->id,
            'club_id' => $clubs[1]->id,
            'type' => 'mixed',
            'venue_name' => 'Cebu Sports Complex',
            'number_of_courts' => 4,
            'location' => 'Cebu Sports Complex, Cebu City',
            'contact_email' => 'info@cebuelitebc.com',
            'contact_phone' => '032-555-6789',
            'tournament_fee' => 500,
            'registration_fee' => 500,
            'start_date' => $now->copy()->addDays(20),
            'end_date' => $now->copy()->addDays(22),
            'registration_deadline' => $now->copy()->addDays(15),
            'withdrawal_deadline' => $now->copy()->addDays(18),
            'status' => 'upcoming',
            'is_dual_meet' => false,
            'bracket_type' => 'single_elimination',
        ]);

        foreach ($categories as $catData) {
            TournamentCategory::create([
                'tournament_id' => $club2AllUpcoming->id,
                'name' => $catData['name'],
                'gender' => $catData['gender'],
                'max_participants' => 16,
                'skill_level' => 'Open',
                'min_age' => null,
                'max_age' => null,
                'schedule_start_date' => $club2AllUpcoming->start_date,
                'schedule_start_time' => '09:00:00',
                'match_duration_minutes' => in_array($catData['type'], ['MS', 'WS']) ? 45 : 60,
                'break_between_matches_minutes' => 5,
            ]);
        }

        $this->command->info('✓ Created Club 2 - All Categories Tournament (Upcoming)');

        // ============================================
        // CREATE DUAL MEET TOURNAMENT
        // ============================================
        $dualMeetTournament = Tournament::create([
            'name' => 'Inter-Club Championship 2025',
            'description' => 'Dual meet tournament between Metro Manila and Cebu clubs.',
            'organizer_id' => $managers[0]->id,
            'club_id' => $clubs[0]->id, // Hosted by Club 1
            'type' => 'mixed',
            'venue_name' => 'National Badminton Center',
            'number_of_courts' => 6,
            'location' => 'National Badminton Center, Manila',
            'contact_email' => 'info@metromanilabc.com',
            'contact_phone' => '02-123-4567',
            'tournament_fee' => 600,
            'registration_fee' => 600,
            'start_date' => $now->copy()->addDays(25),
            'end_date' => $now->copy()->addDays(27),
            'registration_deadline' => $now->copy()->addDays(20),
            'withdrawal_deadline' => $now->copy()->addDays(23),
            'status' => 'upcoming',
            'is_dual_meet' => true, // DUAL MEET TOURNAMENT
            'bracket_type' => 'single_elimination',
        ]);

        // Create categories for dual meet
        $dualMeetMS = TournamentCategory::create([
            'tournament_id' => $dualMeetTournament->id,
            'name' => "Men's Singles",
            'gender' => 'male',
            'max_participants' => 16,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $dualMeetTournament->start_date,
            'schedule_start_time' => '09:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        $dualMeetMD = TournamentCategory::create([
            'tournament_id' => $dualMeetTournament->id,
            'name' => "Men's Doubles",
            'gender' => 'male',
            'max_participants' => 12,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $dualMeetTournament->start_date,
            'schedule_start_time' => '10:00:00',
            'match_duration_minutes' => 60,
            'break_between_matches_minutes' => 5,
        ]);

        // Register players from Club 1 (hosting club)
        TournamentRegistration::create([
            'tournament_id' => $dualMeetTournament->id,
            'category_id' => $dualMeetMS->id,
            'player_id' => $club1Males[0]->id,
            'status' => 'approved',
        ]);

        TournamentRegistration::create([
            'tournament_id' => $dualMeetTournament->id,
            'category_id' => $dualMeetMS->id,
            'player_id' => $club1Males[1]->id,
            'status' => 'approved',
        ]);

        // Register players from Club 2 (different club - should trigger notifications)
        TournamentRegistration::create([
            'tournament_id' => $dualMeetTournament->id,
            'category_id' => $dualMeetMS->id,
            'player_id' => $club2Males[0]->id,
            'status' => 'approved',
        ]);

        TournamentRegistration::create([
            'tournament_id' => $dualMeetTournament->id,
            'category_id' => $dualMeetMS->id,
            'player_id' => $club2Males[1]->id,
            'status' => 'approved',
        ]);

        // Create notification for Club 2 manager (player from their club registered)
        Notification::create([
            'user_id' => $managers[1]->id, // Club 2 manager
            'type' => 'dual_meet_registration',
            'title' => 'Player Registered in Dual Meet Tournament',
            'message' => "One of your players, {$club2Males[0]->first_name} {$club2Males[0]->last_name}, has registered for the dual meet tournament: {$dualMeetTournament->name} (hosted by {$clubs[0]->name}).",
            'data' => [
                'tournament_id' => $dualMeetTournament->id,
                'player_id' => $club2Males[0]->id,
            ],
            'action_url' => '/manager/tournaments/' . $dualMeetTournament->id,
        ]);

        Notification::create([
            'user_id' => $managers[1]->id, // Club 2 manager
            'type' => 'dual_meet_registration',
            'title' => 'Player Registered in Dual Meet Tournament',
            'message' => "One of your players, {$club2Males[1]->first_name} {$club2Males[1]->last_name}, has registered for the dual meet tournament: {$dualMeetTournament->name} (hosted by {$clubs[0]->name}).",
            'data' => [
                'tournament_id' => $dualMeetTournament->id,
                'player_id' => $club2Males[1]->id,
            ],
            'action_url' => '/manager/tournaments/' . $dualMeetTournament->id,
        ]);

        // Register doubles teams (interclub - partners from different clubs)
        // Team 1: Player from Club 1 + Player from Club 2
        TournamentRegistration::create([
            'tournament_id' => $dualMeetTournament->id,
            'category_id' => $dualMeetMD->id,
            'player_id' => $club1Males[2]->id,
            'partner_id' => $club2Males[2]->id, // Partner from different club
            'status' => 'approved',
        ]);
        TournamentRegistration::create([
            'tournament_id' => $dualMeetTournament->id,
            'category_id' => $dualMeetMD->id,
            'player_id' => $club2Males[2]->id,
            'partner_id' => $club1Males[2]->id,
            'status' => 'approved',
        ]);

        // Create notification for Club 2 manager (their player registered as partner)
        Notification::create([
            'user_id' => $managers[1]->id,
            'type' => 'dual_meet_registration',
            'title' => 'Player Registered in Dual Meet Tournament',
            'message' => "One of your players, {$club2Males[2]->first_name} {$club2Males[2]->last_name}, has registered for the dual meet tournament: {$dualMeetTournament->name} (hosted by {$clubs[0]->name}).",
            'data' => [
                'tournament_id' => $dualMeetTournament->id,
                'player_id' => $club2Males[2]->id,
            ],
            'action_url' => '/manager/tournaments/' . $dualMeetTournament->id,
        ]);

        $this->command->info('✓ Created Dual Meet Tournament with interclub registrations and notifications');

        // ============================================
        // CREATE PARTNER INVITATIONS (for doubles registration testing)
        // ============================================
        // Create partner invitation for doubles category in published tournament
        if (isset($club1Published)) {
            $club1PublishedMD = TournamentCategory::create([
                'tournament_id' => $club1Published->id,
                'name' => "Men's Doubles",
                'gender' => 'male',
                'max_participants' => 12,
                'skill_level' => 'Open',
                'min_age' => null,
                'max_age' => null,
                'schedule_start_date' => $club1Published->start_date,
                'schedule_start_time' => '11:00:00',
                'match_duration_minutes' => 60,
                'break_between_matches_minutes' => 5,
            ]);

            // Player 1 invites Player 2 as partner (pending)
            if (count($club1Males) >= 2) {
                PartnerInvitation::create([
                    'tournament_id' => $club1Published->id,
                    'category_id' => $club1PublishedMD->id,
                    'inviter_id' => $club1Males[0]->id,
                    'invitee_id' => $club1Males[1]->id,
                    'status' => 'pending',
                    'message' => 'Would you like to be my doubles partner for this tournament?',
                ]);

                Notification::create([
                    'user_id' => $club1Males[1]->id,
                    'type' => 'partner_invitation',
                    'title' => 'Doubles Partner Invitation',
                    'message' => "{$club1Males[0]->first_name} {$club1Males[0]->last_name} invited you to be their doubles partner for {$club1Published->name}.",
                    'data' => [
                        'tournament_id' => $club1Published->id,
                        'category_id' => $club1PublishedMD->id,
                        'inviter_id' => $club1Males[0]->id,
                    ],
                    'action_url' => route('player.tournaments.register.show', ['tournament' => $club1Published->id, 'category' => $club1PublishedMD->id]),
                ]);

                // Player 3 invites Player 4 as partner (accepted)
                if (count($club1Males) >= 4) {
                    PartnerInvitation::create([
                        'tournament_id' => $club1Published->id,
                        'category_id' => $club1PublishedMD->id,
                        'inviter_id' => $club1Males[2]->id,
                        'invitee_id' => $club1Males[3]->id,
                        'status' => 'accepted',
                        'message' => 'Let\'s team up for this tournament!',
                        'responded_at' => now()->subHours(2),
                    ]);
                    // Seed registrations to reflect accepted team
                    \App\Models\TournamentRegistration::create([
                        'tournament_id' => $club1Published->id,
                        'category_id' => $club1PublishedMD->id,
                        'player_id' => $club1Males[2]->id,
                        'partner_id' => $club1Males[3]->id,
                        'status' => 'approved',
                    ]);
                }
            }
        }

        $this->command->info('✓ Created Partner Invitations for doubles registration testing');

        // ============================================
        // CLEAN UP DUPLICATE CATEGORIES (safety)
        // ============================================
        $duplicates = \DB::table('tournament_categories')
            ->select('tournament_id', 'name', \DB::raw('MIN(id) as keep_id'))
            ->groupBy('tournament_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            \DB::table('tournament_categories')
                ->where('tournament_id', $dup->tournament_id)
                ->where('name', $dup->name)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        // ============================================
        // CREATE DIVISION-SPECIFIC TOURNAMENTS
        // ============================================
        
        // JUNIOR TOURNAMENT (Age < 18, max_age = 17)
        $juniorTournament = Tournament::create([
            'name' => 'Junior Badminton Championship 2025',
            'description' => 'Tournament for Junior division players (Under 18).',
            'organizer_id' => $managers[0]->id,
            'club_id' => $clubs[0]->id,
            'type' => 'mixed',
            'venue_name' => 'Metro Manila Sports Complex',
            'number_of_courts' => 4,
            'location' => 'Metro Manila Sports Complex, Manila',
            'contact_email' => 'info@metromanilabc.com',
            'contact_phone' => '02-123-4567',
            'tournament_fee' => 400,
            'registration_fee' => 400,
            'start_date' => $now->copy()->subDays(30),
            'end_date' => $now->copy()->subDays(28),
            'registration_deadline' => $now->copy()->subDays(35),
            'withdrawal_deadline' => $now->copy()->subDays(33),
            'status' => 'completed',
            'is_dual_meet' => false,
            'bracket_type' => 'single_elimination',
        ]);

        // Junior categories (max_age = 17)
        $juniorMS = TournamentCategory::create([
            'tournament_id' => $juniorTournament->id,
            'name' => "Men's Singles",
            'gender' => 'male',
            'max_participants' => 16,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => 17,
            'schedule_start_date' => $juniorTournament->start_date,
            'schedule_start_time' => '09:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        $juniorWS = TournamentCategory::create([
            'tournament_id' => $juniorTournament->id,
            'name' => "Women's Singles",
            'gender' => 'female',
            'max_participants' => 16,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => 17,
            'schedule_start_date' => $juniorTournament->start_date,
            'schedule_start_time' => '10:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        // Register junior players (from Club 2 - they are juniors)
        $juniorMales = array_filter($club2Males, function($p) {
            return $p->birth_year >= 2007; // Junior players
        });
        $juniorFemales = array_filter($club2Females, function($p) {
            return $p->birth_year >= 2007; // Junior players
        });

        // Register 6 junior males for MS
        foreach (array_slice($juniorMales, 0, 6) as $player) {
            TournamentRegistration::create([
                'tournament_id' => $juniorTournament->id,
                'category_id' => $juniorMS->id,
                'player_id' => $player->id,
                'status' => 'approved',
            ]);
        }

        // Register 6 junior females for WS
        foreach (array_slice($juniorFemales, 0, 6) as $player) {
            TournamentRegistration::create([
                'tournament_id' => $juniorTournament->id,
                'category_id' => $juniorWS->id,
                'player_id' => $player->id,
                'status' => 'approved',
            ]);
        }

        // Generate matches and create results with proper bracket progression
        $matchService->generateMatches($juniorTournament, 'single_elimination');
        $juniorMatches = TournamentMatch::where('tournament_id', $juniorTournament->id)
            ->orderByRaw("CASE 
                WHEN round LIKE 'Round 1%' THEN 1
                WHEN round LIKE 'Round of%' THEN 2
                WHEN round LIKE 'Quarterfinal%' THEN 3
                WHEN round LIKE 'Semifinal%' THEN 4
                WHEN round LIKE 'Final%' THEN 5
                ELSE 6
            END")
            ->orderBy('match_number')
            ->get();
        
        // Group by round
        $matchesByRound = [];
        foreach ($juniorMatches as $match) {
            $roundName = $match->round;
            if (!isset($matchesByRound[$roundName])) {
                $matchesByRound[$roundName] = [];
            }
            $matchesByRound[$roundName][] = $match;
        }
        
        $eloService = app(EloRatingService::class);
        $juniorPlayerWins = []; // Track wins per player
        
        // Process each round, advancing winners
        foreach ($matchesByRound as $roundName => $roundMatches) {
            foreach ($roundMatches as $index => $match) {
                if ($match->player1_id && $match->player2_id) {
                    // Alternate winners to create varied win/loss records
                    $player1Won = ($index % 2 === 0);
                    $winnerId = $player1Won ? $match->player1_id : $match->player2_id;
                    $player1 = User::find($match->player1_id);
                    $player2 = User::find($match->player2_id);
                    
                    $juniorPlayerWins[$winnerId] = ($juniorPlayerWins[$winnerId] ?? 0) + 1;
                    
                    MatchResult::create([
                        'match_id' => $match->id,
                        'player1_set1_score' => $player1Won ? 21 : 15,
                        'player2_set1_score' => $player1Won ? 15 : 21,
                        'player1_set2_score' => $player1Won ? 21 : 18,
                        'player2_set2_score' => $player1Won ? 18 : 21,
                        'winner_id' => $winnerId,
                        'winner_partner_id' => $winnerPartnerId,
                    ]);
                    
                    $match->update([
                        'status' => 'completed',
                        'scheduled_date' => $juniorTournament->start_date,
                        'scheduled_time' => Carbon::parse($juniorTournament->start_date)->setTime(9, 0)->addHours($index),
                        'court_number' => ($index % 4) + 1,
                        'winner_id' => $winnerId,
                    ]);
                    
                    // Calculate ELO
                    if ($player1 && $player2) {
                        $eloService->calculateMatchRatings($player1, $player2, $player1Won, 'MS');
                    }
                    
                    $matchService->advanceWinner($match);
                } elseif ($match->player1_id) {
                    // Bye
                    $match->update([
                        'status' => 'completed',
                        'scheduled_date' => $juniorTournament->start_date,
                        'scheduled_time' => Carbon::parse($juniorTournament->start_date)->setTime(9, 0)->addHours($index),
                        'court_number' => ($index % 4) + 1,
                        'winner_id' => $match->player1_id,
                    ]);
                    $juniorPlayerWins[$match->player1_id] = ($juniorPlayerWins[$match->player1_id] ?? 0) + 1;
                    $matchService->advanceWinner($match);
                }
            }
        }

        $this->command->info('✓ Created Junior Tournament (Completed) with matches and results');

        // SENIOR TOURNAMENT (Age >= 18, min_age = 18)
        $seniorTournament = Tournament::create([
            'name' => 'Senior Badminton Championship 2025',
            'description' => 'Tournament for Senior division players (18 and above).',
            'organizer_id' => $managers[0]->id,
            'club_id' => $clubs[0]->id,
            'type' => 'mixed',
            'venue_name' => 'Metro Manila Sports Complex',
            'number_of_courts' => 4,
            'location' => 'Metro Manila Sports Complex, Manila',
            'contact_email' => 'info@metromanilabc.com',
            'contact_phone' => '02-123-4567',
            'tournament_fee' => 500,
            'registration_fee' => 500,
            'start_date' => $now->copy()->subDays(20),
            'end_date' => $now->copy()->subDays(18),
            'registration_deadline' => $now->copy()->subDays(25),
            'withdrawal_deadline' => $now->copy()->subDays(23),
            'status' => 'completed',
            'is_dual_meet' => false,
            'bracket_type' => 'single_elimination',
        ]);

        // Senior categories (min_age = 18)
        $seniorMS = TournamentCategory::create([
            'tournament_id' => $seniorTournament->id,
            'name' => "Men's Singles",
            'gender' => 'male',
            'max_participants' => 16,
            'skill_level' => 'Open',
            'min_age' => 18,
            'max_age' => null,
            'schedule_start_date' => $seniorTournament->start_date,
            'schedule_start_time' => '09:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        $seniorWS = TournamentCategory::create([
            'tournament_id' => $seniorTournament->id,
            'name' => "Women's Singles",
            'gender' => 'female',
            'max_participants' => 16,
            'skill_level' => 'Open',
            'min_age' => 18,
            'max_age' => null,
            'schedule_start_date' => $seniorTournament->start_date,
            'schedule_start_time' => '10:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        // Register senior players (from Club 1 - they are seniors)
        $seniorMales = array_filter($club1Males, function($p) {
            return $p->birth_year <= 2006; // Senior players
        });
        $seniorFemales = array_filter($club1Females, function($p) {
            return $p->birth_year <= 2006; // Senior players
        });

        // Register 8 senior males for MS
        foreach (array_slice($seniorMales, 0, 8) as $player) {
            TournamentRegistration::create([
                'tournament_id' => $seniorTournament->id,
                'category_id' => $seniorMS->id,
                'player_id' => $player->id,
                'status' => 'approved',
            ]);
        }

        // Register 8 senior females for WS
        foreach (array_slice($seniorFemales, 0, 8) as $player) {
            TournamentRegistration::create([
                'tournament_id' => $seniorTournament->id,
                'category_id' => $seniorWS->id,
                'player_id' => $player->id,
                'status' => 'approved',
            ]);
        }

        // Generate matches and create results with proper bracket progression
        $matchService->generateMatches($seniorTournament, 'single_elimination');
        $seniorMatches = TournamentMatch::where('tournament_id', $seniorTournament->id)
            ->orderByRaw("CASE 
                WHEN round LIKE 'Round 1%' THEN 1
                WHEN round LIKE 'Round of%' THEN 2
                WHEN round LIKE 'Quarterfinal%' THEN 3
                WHEN round LIKE 'Semifinal%' THEN 4
                WHEN round LIKE 'Final%' THEN 5
                ELSE 6
            END")
            ->orderBy('match_number')
            ->get();
        
        // Group by round
        $matchesByRound = [];
        foreach ($seniorMatches as $match) {
            $roundName = $match->round;
            if (!isset($matchesByRound[$roundName])) {
                $matchesByRound[$roundName] = [];
            }
            $matchesByRound[$roundName][] = $match;
        }
        
        $eloService = app(EloRatingService::class);
        $seniorPlayerWins = [];
        
        // Process each round, advancing winners
        foreach ($matchesByRound as $roundName => $roundMatches) {
            foreach ($roundMatches as $index => $match) {
                if ($match->player1_id && $match->player2_id) {
                    // Create varied win/loss patterns
                    $player1Won = ($index % 3 === 0);
                    $winnerId = $player1Won ? $match->player1_id : $match->player2_id;
                    $player1 = User::find($match->player1_id);
                    $player2 = User::find($match->player2_id);
                    
                    $seniorPlayerWins[$winnerId] = ($seniorPlayerWins[$winnerId] ?? 0) + 1;
                    
                    MatchResult::create([
                        'match_id' => $match->id,
                        'player1_set1_score' => $player1Won ? 21 : 15,
                        'player2_set1_score' => $player1Won ? 15 : 21,
                        'player1_set2_score' => $player1Won ? 21 : 18,
                        'player2_set2_score' => $player1Won ? 18 : 21,
                        'winner_id' => $winnerId,
                    ]);
                    
                    $match->update([
                        'status' => 'completed',
                        'scheduled_date' => $seniorTournament->start_date,
                        'scheduled_time' => Carbon::parse($seniorTournament->start_date)->setTime(9, 0)->addHours($index),
                        'court_number' => ($index % 4) + 1,
                        'winner_id' => $winnerId,
                    ]);
                    
                    // Calculate ELO
                    if ($player1 && $player2) {
                        $eloService->calculateMatchRatings($player1, $player2, $player1Won, 'MS');
                    }
                    
                    $matchService->advanceWinner($match);
                } elseif ($match->player1_id) {
                    $match->update([
                        'status' => 'completed',
                        'scheduled_date' => $seniorTournament->start_date,
                        'scheduled_time' => Carbon::parse($seniorTournament->start_date)->setTime(9, 0)->addHours($index),
                        'court_number' => ($index % 4) + 1,
                        'winner_id' => $match->player1_id,
                    ]);
                    $seniorPlayerWins[$match->player1_id] = ($seniorPlayerWins[$match->player1_id] ?? 0) + 1;
                    $matchService->advanceWinner($match);
                }
            }
        }

        $this->command->info('✓ Created Senior Tournament (Completed) with matches and results');

        // ============================================
        // CREATE ADDITIONAL COMPLETED TOURNAMENT FOR RANKINGS TESTING
        // ============================================
        $this->command->info('Creating additional tournament for rankings testing...');
        
        $rankingsTournament = Tournament::create([
            'name' => 'Metro Manila Rankings Championship 2024',
            'description' => 'Completed tournament with many matches for rankings testing.',
            'organizer_id' => $managers[0]->id,
            'club_id' => $clubs[0]->id,
            'type' => 'mixed',
            'venue_name' => 'Metro Manila Sports Complex',
            'number_of_courts' => 4,
            'location' => 'Metro Manila Sports Complex, Manila',
            'contact_email' => 'info@metromanilabc.com',
            'contact_phone' => '02-123-4567',
            'tournament_fee' => 500,
            'registration_fee' => 500,
            'start_date' => $now->copy()->subDays(60),
            'end_date' => $now->copy()->subDays(57),
            'registration_deadline' => $now->copy()->subDays(65),
            'withdrawal_deadline' => $now->copy()->subDays(62),
            'status' => 'completed',
            'is_dual_meet' => false,
            'bracket_type' => 'single_elimination',
        ]);

        // Create Men's Singles with 16 players for full bracket
        $rankingsMS = TournamentCategory::create([
            'tournament_id' => $rankingsTournament->id,
            'name' => "Men's Singles",
            'gender' => 'male',
            'max_participants' => 16,
            'skill_level' => 'Open',
            'min_age' => null,
            'max_age' => null,
            'schedule_start_date' => $rankingsTournament->start_date,
            'schedule_start_time' => '09:00:00',
            'match_duration_minutes' => 45,
            'break_between_matches_minutes' => 5,
        ]);

        // Register 16 players for full bracket (Round 1 → Round of 16 → Quarterfinals → Semifinals → Finals)
        for ($i = 0; $i < 16 && $i < count($allMales); $i++) {
            TournamentRegistration::create([
                'tournament_id' => $rankingsTournament->id,
                'category_id' => $rankingsMS->id,
                'player_id' => $allMales[$i]->id,
                'status' => 'approved',
            ]);
        }

        // Generate and complete all matches with proper bracket progression
        $matchService->generateMatches($rankingsTournament, 'single_elimination');
        $rankingsMatches = TournamentMatch::where('tournament_id', $rankingsTournament->id)
            ->where('tournament_category_id', $rankingsMS->id)
            ->orderByRaw("CASE 
                WHEN round LIKE 'Round 1%' THEN 1
                WHEN round LIKE 'Round of 16%' THEN 2
                WHEN round LIKE 'Quarterfinal%' THEN 3
                WHEN round LIKE 'Semifinal%' THEN 4
                WHEN round LIKE 'Final%' THEN 5
                ELSE 6
            END")
            ->orderBy('match_number')
            ->get();

        $eloService = app(EloRatingService::class);
        $matchesByRound = [];
        foreach ($rankingsMatches as $match) {
            $roundName = $match->round;
            if (!isset($matchesByRound[$roundName])) {
                $matchesByRound[$roundName] = [];
            }
            $matchesByRound[$roundName][] = $match;
        }

        // Process each round, advancing winners
        foreach ($matchesByRound as $roundName => $roundMatches) {
            foreach ($roundMatches as $index => $match) {
                if ($match->player1_id && $match->player2_id) {
                    // Alternate winners for variety in rankings
                    $player1Won = ($index % 2 === 0);
                    $winnerId = $player1Won ? $match->player1_id : $match->player2_id;
                    $player1 = User::find($match->player1_id);
                    $player2 = User::find($match->player2_id);
                    
                    MatchResult::create([
                        'match_id' => $match->id,
                        'player1_set1_score' => $player1Won ? 21 : 15,
                        'player2_set1_score' => $player1Won ? 15 : 21,
                        'player1_set2_score' => $player1Won ? 21 : 18,
                        'player2_set2_score' => $player1Won ? 18 : 21,
                        'winner_id' => $winnerId,
                    ]);
                    
                    $match->update([
                        'status' => 'completed',
                        'scheduled_date' => $rankingsTournament->start_date,
                        'scheduled_time' => Carbon::parse($rankingsTournament->start_date)->setTime(9, 0)->addHours($index),
                        'court_number' => ($index % 4) + 1,
                        'winner_id' => $winnerId,
                    ]);
                    
                    // Calculate ELO
                    if ($player1 && $player2) {
                        $eloService->calculateMatchRatings($player1, $player2, $player1Won, 'MS');
                    }
                    
                    $matchService->advanceWinner($match);
                } elseif ($match->player1_id) {
                    // Bye
                    $match->update([
                        'status' => 'completed',
                        'scheduled_date' => $rankingsTournament->start_date,
                        'scheduled_time' => Carbon::parse($rankingsTournament->start_date)->setTime(9, 0)->addHours($index),
                        'court_number' => ($index % 4) + 1,
                        'winner_id' => $match->player1_id,
                    ]);
                    $matchService->advanceWinner($match);
                }
            }
        }

        $this->command->info('✓ Created Rankings Tournament with full bracket progression');

        // ============================================
        // VERIFY ALL COMPLETED TOURNAMENTS HAVE FULLY COMPLETED MATCHES
        // ============================================
        $this->command->info('Verifying all completed tournaments have fully completed matches...');
        
        $completedTournaments = Tournament::where('status', 'completed')->get();
        foreach ($completedTournaments as $tournament) {
            $allMatches = TournamentMatch::where('tournament_id', $tournament->id)->get();
            $matchesWithoutResults = [];
            
            foreach ($allMatches as $match) {
                // Skip bye matches (only player1_id, no player2_id)
                if ($match->player1_id && !$match->player2_id) {
                    continue; // Bye matches don't need results
                }
                
                // Check if match has a result
                if (!$match->result) {
                    $matchesWithoutResults[] = $match->id;
                } else {
                    // Verify match status is completed
                    if ($match->status !== 'completed') {
                        $match->update(['status' => 'completed']);
                    }
                }
            }
            
            if (count($matchesWithoutResults) > 0) {
                $this->command->warn("Tournament {$tournament->name} has " . count($matchesWithoutResults) . " matches without results. Creating results...");
                
                // Create results for missing matches
                foreach ($matchesWithoutResults as $matchId) {
                    $match = TournamentMatch::find($matchId);
                    if ($match && $match->player1_id && $match->player2_id) {
                        // Determine winner (alternate for variety)
                        $player1Won = (rand(0, 1) === 1);
                        $winnerId = $player1Won ? $match->player1_id : $match->player2_id;
                        $winnerPartnerId = $player1Won ? $match->player1_partner_id : $match->player2_partner_id;
                        
                        MatchResult::create([
                            'match_id' => $match->id,
                            'player1_set1_score' => $player1Won ? 21 : 15,
                            'player2_set1_score' => $player1Won ? 15 : 21,
                            'player1_set2_score' => $player1Won ? 21 : 18,
                            'player2_set2_score' => $player1Won ? 18 : 21,
                            'winner_id' => $winnerId,
                        ]);
                        
                        $match->update([
                            'status' => 'completed',
                            'winner_id' => $winnerId,
                            'winner_partner_id' => $winnerPartnerId,
                        ]);
                    }
                }
            }
        }
        
        $this->command->info('✓ Verified all completed tournaments have fully completed matches');

        // ============================================
        // SUMMARY
        // ============================================
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('TEST DATA CREATED SUCCESSFULLY');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('CLUBS & MANAGERS:');
        $this->command->info('  Club 1: Metro Manila Badminton Club');
        $this->command->info('    Manager: alex.rodriguez@test.com (password: password)');
        $this->command->info('  Club 2: Cebu Elite Badminton Club');
        $this->command->info('    Manager: sarah.chen@test.com (password: password)');
        $this->command->info('');
        $this->command->info('TOURNAMENTS (11 total):');
        $this->command->info('');
        $this->command->info('CLUB 1:');
        $this->command->info('  1. Metro Manila Singles Open 2025 (PUBLISHED)');
        $this->command->info('     - Men\'s Singles, Women\'s Singles, Men\'s Doubles');
        $this->command->info('     - Test: Published status, partner invitations');
        $this->command->info('');
        $this->command->info('  3. Metro Manila Summer Open 2025 (UPCOMING)');
        $this->command->info('     - Men\'s Singles, Women\'s Singles');
        $this->command->info('     - Test: Registration, eligibility, withdrawal, payment');
        $this->command->info('');
        $this->command->info('  4. Metro Manila Doubles Championship 2025 (ONGOING)');
        $this->command->info('     - Men\'s Doubles (Round of 12)');
        $this->command->info('     - Match at 11:30 PM today (ongoing)');
        $this->command->info('     - One match rescheduled (for testing)');
        $this->command->info('     - Test: Result input, late input, bracket updates, ELO, rescheduling');
        $this->command->info('');
        $this->command->info('  5. Metro Manila Grand Championship 2024 (COMPLETED)');
        $this->command->info('     - All 5 categories with completed brackets');
        $this->command->info('     - Test: View results, brackets, ELO updates');
        $this->command->info('');
        $this->command->info('  6. Junior Badminton Championship 2025 (COMPLETED)');
        $this->command->info('     - Junior Division (Under 18)');
        $this->command->info('     - Men\'s Singles, Women\'s Singles');
        $this->command->info('     - Test: Division filtering, ranking by division');
        $this->command->info('');
        $this->command->info('  7. Senior Badminton Championship 2025 (COMPLETED)');
        $this->command->info('     - Senior Division (18 and above)');
        $this->command->info('     - Men\'s Singles, Women\'s Singles');
        $this->command->info('     - Test: Division filtering, ranking by division');
        $this->command->info('');
        $this->command->info('  8. Metro Manila Rankings Championship 2024 (COMPLETED)');
        $this->command->info('     - Men\'s Singles (16 players - full bracket)');
        $this->command->info('     - Round 1 → Round of 16 → Quarterfinals → Semifinals → Finals');
        $this->command->info('     - All matches completed with proper bracket progression');
        $this->command->info('     - Test: Rankings, ELO calculations, player statistics');
        $this->command->info('');
        $this->command->info('CLUB 2:');
        $this->command->info('  1. Cebu Singles Championship 2025 (ONGOING)');
        $this->command->info('     - Men\'s Singles (Round of 12)');
        $this->command->info('     - Round 1 completed, 2 Quarterfinal matches need result input');
        $this->command->info('     - Test: Match management, result input, rescheduling');
        $this->command->info('');
        $this->command->info('  2. Cebu Doubles Championship 2024 (COMPLETED)');
        $this->command->info('     - Men\'s Doubles, Women\'s Doubles, Mixed Doubles');
        $this->command->info('     - Test: Completed brackets, results display');
        $this->command->info('');
        $this->command->info('  3. Cebu Grand Open 2025 (UPCOMING)');
        $this->command->info('     - All 5 categories (Open Division - All Ages)');
        $this->command->info('     - Test: Create/Generate tournament flow');
        $this->command->info('');
        $this->command->info('DUAL MEET TOURNAMENT:');
        $this->command->info('  Inter-Club Championship 2025 (UPCOMING)');
        $this->command->info('    - Hosted by: Metro Manila Badminton Club');
        $this->command->info('    - Men\'s Singles, Men\'s Doubles');
        $this->command->info('    - Players from both clubs registered');
        $this->command->info('    - Test: Dual meet notifications, interclub registrations');
        $this->command->info('');
        $this->command->info('PLAYER DIVISIONS:');
        $this->command->info('  - Club 1: Senior players (age >= 18, birth_year <= 2006)');
        $this->command->info('  - Club 2: Junior players (age < 18, birth_year >= 2007)');
        $this->command->info('  - All players have proper birth dates for division calculation');
        $this->command->info('');
        $this->command->info('CLUB INVITATIONS:');
        $this->command->info('  - 3 pending invitations created');
        $this->command->info('  - 4 unaffiliated players available for invitation testing');
        $this->command->info('  - Test: Accept/reject invitations, notifications');
        $this->command->info('');
        $this->command->info('PARTNER INVITATIONS:');
        $this->command->info('  - 1 pending partner invitation (for doubles)');
        $this->command->info('  - 1 accepted partner invitation');
        $this->command->info('  - Test: Partner invitation workflow, notifications');
        $this->command->info('');
        $this->command->info('RESCHEDULED MATCHES:');
        $this->command->info('  - 1 rescheduled match in ongoing tournament');
        $this->command->info('  - Test: Rescheduling notifications, match updates');
        $this->command->info('');
        $this->command->info('RANKINGS & STATISTICS:');
        $this->command->info('  - ELO ratings calculated using EloRatingService based on match results');
        $this->command->info('  - Ranking history entries created for all match results');
        $this->command->info('  - Players have varied match counts (some with many matches, some with few)');
        $this->command->info('  - High and low ELO players included for rankings testing');
        $this->command->info('  - Win/loss records match actual match results');
        $this->command->info('');
        $this->command->info('COMPLETED TOURNAMENTS:');
        $this->command->info('  - All matches completed with proper bracket progression');
        $this->command->info('  - No TBD players in completed tournaments');
        $this->command->info('  - All rounds properly connected (Round 1 → Quarterfinals → Semifinals → Finals)');
        $this->command->info('  - Winners advanced correctly through brackets');
        $this->command->info('  - Match results and scores properly recorded');
        $this->command->info('');
        $this->command->info('ONGOING TOURNAMENTS:');
        $this->command->info('  - At least 2 matches require result input for testing');
        $this->command->info('  - Some matches completed, some scheduled, some ongoing');
        $this->command->info('');
        $this->command->info('ALL PLAYERS: password = "password"');
        $this->command->info('All players have ELO ratings for all relevant categories.');
        $this->command->info('Unaffiliated players: carlos.rivera@test.com, elena.martinez@test.com, roberto.gomez@test.com, isabella.lopez@test.com');
        $this->command->info('');
    }
}
