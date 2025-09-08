-- ========================================
-- REWARD SYSTEM VERIFICATION QUERIES
-- ========================================

-- 1. OVERVIEW: System Statistics
SELECT 
    'Total Active Users' as metric,
    COUNT(*) as count
FROM users 
WHERE blocked = 0 AND can_login = 1

UNION ALL

SELECT 
    'Total Rewards Paid' as metric,
    CONCAT('$', FORMAT(COALESCE(SUM(balance), 0), 2)) as count
FROM wallets 
WHERE wallet_type = 'reward'

UNION ALL

SELECT 
    'Pending Rewards' as metric,
    COUNT(*) as count
FROM pending_rewards 
WHERE status = 'pending'

UNION ALL

SELECT 
    'Approved Rewards' as metric,
    COUNT(*) as count
FROM pending_rewards 
WHERE status = 'approved'

UNION ALL

SELECT 
    'Denied Rewards' as metric,
    COUNT(*) as count
FROM pending_rewards 
WHERE status = 'denied';

-- ========================================
-- 2. TOP USERS BY TEAM SIZE
-- ========================================
SELECT 
    u.id,
    u.name,
    u.email,
    COUNT(rt.descendant_id) as total_team_size,
    CONCAT('Level 1: ', 
        (SELECT COUNT(*) FROM referral_trees rt2 
         JOIN users u2 ON rt2.descendant_id = u2.id 
         WHERE rt2.ancestor_id = u.id AND rt2.level = 1 AND u2.blocked = 0 AND u2.can_login = 1)
    ) as level_1_count
FROM users u
LEFT JOIN referral_trees rt ON u.id = rt.ancestor_id
LEFT JOIN users u_desc ON rt.descendant_id = u_desc.id AND u_desc.blocked = 0 AND u_desc.can_login = 1
WHERE u.blocked = 0 AND u.can_login = 1
GROUP BY u.id, u.name, u.email
ORDER BY total_team_size DESC
LIMIT 15;

-- ========================================
-- 3. DETAILED TEAM STRUCTURE FOR SPECIFIC USER
-- Replace USER_ID with actual user ID
-- ========================================
SET @user_id = 1; -- Change this to the user ID you want to check

SELECT 
    @user_id as user_id,
    u.name as user_name,
    'Team Structure' as info_type,
    CONCAT(
        'L1: ', COALESCE(l1.count, 0), ' (need 10), ',
        'L2: ', COALESCE(l2.count, 0), ' (need 20), ',
        'L3: ', COALESCE(l3.count, 0), ' (need 30), ',
        'L4: ', COALESCE(l4.count, 0), ' (need 40), ',
        'L5: ', COALESCE(l5.count, 0), ' (need 50), ',
        'L6: ', COALESCE(l6.count, 0), ' (need 60), ',
        'L7: ', COALESCE(l7.count, 0), ' (need 70)'
    ) as details
FROM users u
LEFT JOIN (
    SELECT ancestor_id, COUNT(*) as count 
    FROM referral_trees rt 
    JOIN users u ON rt.descendant_id = u.id 
    WHERE level = 1 AND u.blocked = 0 AND u.can_login = 1 
    GROUP BY ancestor_id
) l1 ON u.id = l1.ancestor_id
LEFT JOIN (
    SELECT ancestor_id, COUNT(*) as count 
    FROM referral_trees rt 
    JOIN users u ON rt.descendant_id = u.id 
    WHERE level = 2 AND u.blocked = 0 AND u.can_login = 1 
    GROUP BY ancestor_id
) l2 ON u.id = l2.ancestor_id
LEFT JOIN (
    SELECT ancestor_id, COUNT(*) as count 
    FROM referral_trees rt 
    JOIN users u ON rt.descendant_id = u.id 
    WHERE level = 3 AND u.blocked = 0 AND u.can_login = 1 
    GROUP BY ancestor_id
) l3 ON u.id = l3.ancestor_id
LEFT JOIN (
    SELECT ancestor_id, COUNT(*) as count 
    FROM referral_trees rt 
    JOIN users u ON rt.descendant_id = u.id 
    WHERE level = 4 AND u.blocked = 0 AND u.can_login = 1 
    GROUP BY ancestor_id
) l4 ON u.id = l4.ancestor_id
LEFT JOIN (
    SELECT ancestor_id, COUNT(*) as count 
    FROM referral_trees rt 
    JOIN users u ON rt.descendant_id = u.id 
    WHERE level = 5 AND u.blocked = 0 AND u.can_login = 1 
    GROUP BY ancestor_id
) l5 ON u.id = l5.ancestor_id
LEFT JOIN (
    SELECT ancestor_id, COUNT(*) as count 
    FROM referral_trees rt 
    JOIN users u ON rt.descendant_id = u.id 
    WHERE level = 6 AND u.blocked = 0 AND u.can_login = 1 
    GROUP BY ancestor_id
) l6 ON u.id = l6.ancestor_id
LEFT JOIN (
    SELECT ancestor_id, COUNT(*) as count 
    FROM referral_trees rt 
    JOIN users u ON rt.descendant_id = u.id 
    WHERE level = 7 AND u.blocked = 0 AND u.can_login = 1 
    GROUP BY ancestor_id
) l7 ON u.id = l7.ancestor_id
WHERE u.id = @user_id;

-- ========================================
-- 4. CURRENT REWARDS FOR SPECIFIC USER
-- ========================================
SELECT 
    w.user_id,
    u.name,
    w.level,
    w.balance,
    w.total_amount,
    w.created_at as reward_granted_at
FROM wallets w
JOIN users u ON w.user_id = u.id
WHERE w.user_id = @user_id 
    AND w.wallet_type = 'reward' 
    AND w.commission_type = 'reward'
ORDER BY w.level;

-- ========================================
-- 5. PENDING REWARDS FOR SPECIFIC USER
-- ========================================
SELECT 
    pr.id,
    pr.user_id,
    u.name,
    pr.level,
    pr.reward_amount,
    pr.team_count as original_team_count,
    pr.users_required,
    pr.status,
    pr.admin_notes,
    pr.created_at,
    CASE 
        WHEN pr.approved_by IS NOT NULL 
        THEN (SELECT name FROM users WHERE id = pr.approved_by)
        ELSE NULL
    END as approved_by_name
FROM pending_rewards pr
JOIN users u ON pr.user_id = u.id
WHERE pr.user_id = @user_id
ORDER BY pr.level;

-- ========================================
-- 6. FIND USERS WHO SHOULD BE ELIGIBLE FOR LEVEL 1 REWARDS
-- ========================================
SELECT 
    u.id,
    u.name,
    u.email,
    level_1_count,
    CASE 
        WHEN existing_reward.user_id IS NOT NULL THEN 'Has Reward'
        WHEN pending_reward.user_id IS NOT NULL THEN 'Has Pending'
        ELSE 'ELIGIBLE FOR LEVEL 1!'
    END as status
FROM (
    SELECT 
        rt.ancestor_id as user_id,
        COUNT(*) as level_1_count
    FROM referral_trees rt
    JOIN users u ON rt.descendant_id = u.id
    WHERE rt.level = 1 AND u.blocked = 0 AND u.can_login = 1
    GROUP BY rt.ancestor_id
    HAVING COUNT(*) >= 10
) eligible_users
JOIN users u ON eligible_users.user_id = u.id
LEFT JOIN (
    SELECT DISTINCT user_id 
    FROM wallets 
    WHERE wallet_type = 'reward' AND level = 1 AND balance > 0
) existing_reward ON u.id = existing_reward.user_id
LEFT JOIN (
    SELECT DISTINCT user_id 
    FROM pending_rewards 
    WHERE level = 1 AND status IN ('pending', 'approved')
) pending_reward ON u.id = pending_reward.user_id
WHERE u.blocked = 0 AND u.can_login = 1
ORDER BY level_1_count DESC;

-- ========================================
-- 7. REWARD LEVEL REQUIREMENTS vs ACTUAL
-- ========================================
SELECT 
    'Level 1' as level,
    10 as required_team,
    130.00 as reward_amount,
    COUNT(*) as users_eligible
FROM (
    SELECT rt.ancestor_id
    FROM referral_trees rt
    JOIN users u ON rt.descendant_id = u.id
    WHERE rt.level = 1 AND u.blocked = 0 AND u.can_login = 1
    GROUP BY rt.ancestor_id
    HAVING COUNT(*) >= 10
) level1

UNION ALL

SELECT 
    'Level 2' as level,
    20 as required_team,
    350.00 as reward_amount,
    COUNT(*) as users_eligible
FROM (
    SELECT rt.ancestor_id
    FROM referral_trees rt
    JOIN users u ON rt.descendant_id = u.id
    WHERE rt.level = 2 AND u.blocked = 0 AND u.can_login = 1
    GROUP BY rt.ancestor_id
    HAVING COUNT(*) >= 20
) level2

UNION ALL

SELECT 
    'Level 3' as level,
    30 as required_team,
    1050.00 as reward_amount,
    COUNT(*) as users_eligible
FROM (
    SELECT rt.ancestor_id
    FROM referral_trees rt
    JOIN users u ON rt.descendant_id = u.id
    WHERE rt.level = 3 AND u.blocked = 0 AND u.can_login = 1
    GROUP BY rt.ancestor_id
    HAVING COUNT(*) >= 30
) level3;

-- ========================================
-- 8. CHECK REFERRAL TREES INTEGRITY
-- ========================================
SELECT 
    'Referral Trees Check' as check_type,
    COUNT(*) as total_relationships,
    COUNT(DISTINCT ancestor_id) as unique_ancestors,
    COUNT(DISTINCT descendant_id) as unique_descendants,
    MIN(level) as min_level,
    MAX(level) as max_level
FROM referral_trees;

-- ========================================
-- 9. INCONSISTENCIES CHECK
-- Users with rewards but insufficient team
-- ========================================
SELECT 
    'Potential Issues' as issue_type,
    w.user_id,
    u.name,
    w.level as reward_level,
    w.balance,
    COALESCE(team_count.count, 0) as current_team_count,
    CASE w.level 
        WHEN 1 THEN 10
        WHEN 2 THEN 20  
        WHEN 3 THEN 30
        WHEN 4 THEN 40
        WHEN 5 THEN 50
        WHEN 6 THEN 60
        WHEN 7 THEN 70
    END as required_count,
    'Has reward but insufficient team' as issue_description
FROM wallets w
JOIN users u ON w.user_id = u.id
LEFT JOIN (
    SELECT 
        rt.ancestor_id,
        rt.level,
        COUNT(*) as count
    FROM referral_trees rt
    JOIN users u ON rt.descendant_id = u.id
    WHERE u.blocked = 0 AND u.can_login = 1
    GROUP BY rt.ancestor_id, rt.level
) team_count ON w.user_id = team_count.ancestor_id AND w.level = team_count.level
WHERE w.wallet_type = 'reward' 
    AND w.balance > 0
    AND COALESCE(team_count.count, 0) < CASE w.level 
        WHEN 1 THEN 10
        WHEN 2 THEN 20  
        WHEN 3 THEN 30
        WHEN 4 THEN 40
        WHEN 5 THEN 50
        WHEN 6 THEN 60
        WHEN 7 THEN 70
    END;

-- ========================================
-- 10. SAMPLE DATA CHECK
-- Shows first 10 users with their team structure
-- ========================================
SELECT 
    u.id,
    u.name,
    u.email,
    COALESCE(l1_count, 0) as level_1_team,
    COALESCE(reward_count, 0) as rewards_received,
    COALESCE(pending_count, 0) as pending_rewards,
    CASE 
        WHEN COALESCE(l1_count, 0) >= 10 AND COALESCE(reward_count, 0) = 0 AND COALESCE(pending_count, 0) = 0 
        THEN '🎯 ELIGIBLE FOR PROCESSING'
        WHEN COALESCE(reward_count, 0) > 0 
        THEN '✅ HAS REWARDS'
        WHEN COALESCE(pending_count, 0) > 0 
        THEN '⏳ HAS PENDING'
        ELSE '❌ NOT ELIGIBLE YET'
    END as status
FROM users u
LEFT JOIN (
    SELECT ancestor_id, COUNT(*) as l1_count
    FROM referral_trees rt
    JOIN users u ON rt.descendant_id = u.id
    WHERE rt.level = 1 AND u.blocked = 0 AND u.can_login = 1
    GROUP BY ancestor_id
) team ON u.id = team.ancestor_id
LEFT JOIN (
    SELECT user_id, COUNT(*) as reward_count
    FROM wallets
    WHERE wallet_type = 'reward' AND balance > 0
    GROUP BY user_id
) rewards ON u.id = rewards.user_id
LEFT JOIN (
    SELECT user_id, COUNT(*) as pending_count
    FROM pending_rewards
    WHERE status IN ('pending', 'approved')
    GROUP BY user_id
) pending ON u.id = pending.user_id
WHERE u.blocked = 0 AND u.can_login = 1
ORDER BY COALESCE(l1_count, 0) DESC
LIMIT 10;