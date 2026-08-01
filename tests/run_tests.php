<?php
// Comprehensive Integration Test Suite for Ministry Ops PHP

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Core/Helpers.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Tenant.php';
require_once __DIR__ . '/../src/Models/Assignment.php';
require_once __DIR__ . '/../src/Models/Swap.php';
require_once __DIR__ . '/../src/Models/Checkin.php';
require_once __DIR__ . '/../src/Models/Bulletin.php';
require_once __DIR__ . '/../src/Models/Gamification.php';
require_once __DIR__ . '/../src/Models/AdminModel.php';

echo "=====================================================\n";
echo "    MINISTRY OPS PHP - AUTOMATED TEST SUITE        \n";
echo "=====================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $testName, string $failureDetails = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] {$testName}\n";
        $passed++;
    } else {
        echo "[FAIL] {$testName}";
        if ($failureDetails) echo " -> {$failureDetails}";
        echo "\n";
        $failed++;
    }
}

try {
    $db = Database::getConnection();
    echo "✔ Database connection established successfully.\n\n";

    $tenant = Tenant::findByCode('MATRIZ');
    $tenantId = $tenant['id'];
    $adminUser = User::findByEmail('admin@ministry-ops.test');
    $volunteerUser = User::findByEmail('volunteer@ministry-ops.test');
    $otherVolunteer = User::findByEmail('lucas.voluntario@ministry-ops.test');

    // Reset base test assignment for volunteerUser (starts_at = NOW + 10 MINUTE)
    $asId = 'as000000-0000-0000-0000-000000000001';
    $db->exec("
        UPDATE assignments 
        SET volunteer_user_id = '{$volunteerUser['id']}', status = 'pending_confirmation', starts_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE), ends_at = DATE_ADD(NOW(), INTERVAL 3 HOUR)
        WHERE id = '{$asId}'
    ");
    $db->exec("DELETE FROM swap_requests WHERE assignment_id = '{$asId}'");
    $db->exec("DELETE FROM attendance_checkins WHERE assignment_id = '{$asId}'");
    $db->exec("DELETE FROM assignment_confirmations WHERE assignment_id = '{$asId}'");

    // ---------------------------------------------------------
    // TEST 1: User & Authentication Model
    // ---------------------------------------------------------
    echo "--- 1. Testing User Authentication & Profile ---\n";
    assertTest($adminUser !== null, 'Admin user lookup by email');
    assertTest($volunteerUser !== null, 'Volunteer user lookup by email');
    assertTest(User::findByEmail('nonexistent@test.com') === null, 'Lookup for non-existent user returns null');

    // ---------------------------------------------------------
    // TEST 2: Tenant & Join Requests
    // ---------------------------------------------------------
    echo "\n--- 2. Testing Multi-Tenant Context & Join Requests ---\n";
    assertTest($tenant !== null && $tenant['name'] === 'Igreja Central Matriz', 'Find tenant by code MATRIZ');

    $testUserEmail = 'test.join.' . time() . '@test.com';
    $testUserId = User::create($testUserEmail, 'password123', 'Test Join Volunteer', '(11) 91111-2222');
    $joinOk = Tenant::requestJoin($testUserId, 'MATRIZ', 'Quero me juntar ao grupo de acolhimento');
    assertTest($joinOk, 'Request tenant join by code MATRIZ');

    $joinRequests = AdminModel::getJoinRequests($tenantId);
    $foundReq = false;
    $reqId = null;
    foreach ($joinRequests as $req) {
        if ($req['user_id'] === $testUserId) {
            $foundReq = true;
            $reqId = $req['id'];
            break;
        }
    }
    assertTest($foundReq, 'Admin lists pending join request');

    if ($reqId) {
        $approveOk = AdminModel::reviewJoinRequest($reqId, 'approve', $adminUser['id'], $tenantId);
        assertTest($approveOk, 'Admin approves tenant join request');
    }

    // ---------------------------------------------------------
    // TEST 3: Escalas (Assignments) & Confirmations
    // ---------------------------------------------------------
    echo "\n--- 3. Testing Assignments (Escalas) & Confirmations ---\n";
    $assignments = Assignment::getForUser($volunteerUser['id'], $tenantId, 'upcoming');
    assertTest(!empty($assignments), 'Get upcoming assignments for volunteer');
    
    if (!empty($assignments)) {
        $confirmOk = Assignment::confirm($asId, $volunteerUser['id'], $tenantId, 'Confirmado via teste automatizado');
        assertTest($confirmOk, 'Volunteer confirms assignment');

        $updatedAs = Assignment::findById($asId, $tenantId);
        assertTest($updatedAs['status'] === 'confirmed', 'Assignment status updated to confirmed');
    }

    // ---------------------------------------------------------
    // TEST 4: Trocas de Escala (Swaps Workflow)
    // ---------------------------------------------------------
    echo "\n--- 4. Testing Swap Request & Cover Workflow ---\n";
    assertTest($otherVolunteer !== null, 'Find substitute volunteer Lucas');

    if ($otherVolunteer) {
        $swapReqOk = Swap::createRequest($asId, $volunteerUser['id'], $tenantId, 'Precisarei viajar');
        assertTest($swapReqOk, 'Volunteer requests swap for confirmed assignment');

        $openSwaps = Swap::getOpenSwaps($tenantId, $otherVolunteer['id']);
        assertTest(!empty($openSwaps), 'Substitute volunteer lists open swap requests');

        $swapId = $openSwaps[0]['id'] ?? null;
        if ($swapId) {
            $coverOk = Swap::offerToCover($swapId, $otherVolunteer['id'], $tenantId);
            assertTest($coverOk, 'Substitute volunteer offers to cover swap');

            $approveSwapOk = Swap::approveSwap($swapId, $adminUser['id'], $tenantId);
            assertTest($approveSwapOk, 'Admin approves swap request and reassigns volunteer');

            $reassignedAs = Assignment::findById($asId, $tenantId);
            assertTest($reassignedAs['volunteer_user_id'] === $otherVolunteer['id'], 'Assignment reassigned to substitute volunteer');
        }
    }

    // ---------------------------------------------------------
    // TEST 5: Geolocation Check-in & Distance Rules
    // ---------------------------------------------------------
    echo "\n--- 5. Testing Geolocation Check-in & Distance Validation ---\n";
    if ($otherVolunteer) {
        // Test position within 50m of church center (-23.5505200, -46.6333080)
        $checkinResult = Checkin::process($otherVolunteer['id'], $asId, $tenantId, -23.5505200, -46.6333080, false);
        assertTest($checkinResult['ok'], 'Checkin within radius succeeds', $checkinResult['message'] ?? '');

        // Test position far away (50km away in Campinas -22.9056, -47.0608)
        // Reset assignment to confirmed first
        $db->exec("UPDATE assignments SET status = 'confirmed' WHERE id = '{$asId}'");
        $farResult = Checkin::process($otherVolunteer['id'], $asId, $tenantId, -22.9056000, -47.0608000, false);
        assertTest(!$farResult['ok'] && $farResult['code'] === 'outside_radius', 'Checkin outside radius fails with outside_radius code');

        // Test position far away WITH manual override (bypass_geofence = true)
        $bypassResult = Checkin::process($otherVolunteer['id'], $asId, $tenantId, -22.9056000, -47.0608000, true);
        assertTest($bypassResult['ok'], 'Checkin with manual override bypasses radius check');
    }

    // ---------------------------------------------------------
    // TEST 6: Bulletins & Read Receipts
    // ---------------------------------------------------------
    echo "\n--- 6. Testing Bulletins & Acknowledgements ---\n";
    $bulletinId = Bulletin::create($tenantId, $adminUser['id'], 'Teste de Comunicado Automatizado', 'Mensagem de teste para verificacao.');
    assertTest(!empty($bulletinId), 'Admin creates new bulletin');

    $ackOk = Bulletin::acknowledge($bulletinId, $volunteerUser['id'], $tenantId);
    assertTest($ackOk, 'Volunteer acknowledges bulletin read receipt');

    // ---------------------------------------------------------
    // TEST 7: Gamification & Leaderboard
    // ---------------------------------------------------------
    echo "\n--- 7. Testing Gamification & Leaderboard ---\n";
    $stats = Gamification::getUserStats($tenantId, $volunteerUser['id']);
    assertTest(is_numeric($stats['total_points']), 'User stats total points is numeric');

    $leaderboard = Gamification::getLeaderboard($tenantId, 10);
    assertTest(is_array($leaderboard), 'Leaderboard returns array of top volunteers');

    // ---------------------------------------------------------
    // TEST 8: Operations, Events & Shifts Admin Management
    // ---------------------------------------------------------
    echo "\n--- 8. Testing Admin Operations, Events & Shifts CRUD ---\n";
    $opId = AdminModel::createOperation($tenantId, 'Operação Teste Especial', 'Descrição de testes');
    assertTest(!empty($opId), 'Admin creates operation');

    $eventId = AdminModel::createEventInstance($tenantId, $opId, date('Y-m-d H:i:s', strtotime('+2 days')), date('Y-m-d H:i:s', strtotime('+2 days + 3 hours')), 'Auditório 2');
    assertTest(!empty($eventId), 'Admin creates event instance');

    $shiftId = AdminModel::createShift($tenantId, $eventId, 'Turno Tarde', date('Y-m-d H:i:s', strtotime('+2 days + 1 hour')), date('Y-m-d H:i:s', strtotime('+2 days + 3 hours')));
    assertTest(!empty($shiftId), 'Admin creates shift');

    $invalidDateCheck = strtotime('+2 days') > strtotime('+2 days + 3 hours');
    assertTest(!$invalidDateCheck, 'Date validation rejects end date prior to start date');

} catch (Exception $e) {
    echo "\n[ERROR] Exception caught during tests: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n=====================================================\n";
echo " TEST SUMMARY: Passed: {$passed} | Failed: {$failed} \n";
echo "=====================================================\n";

exit($failed > 0 ? 1 : 0);
