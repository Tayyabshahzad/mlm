-- Debug Referral Tree for User ID 2
-- Check if referral_trees table has correct level calculations

-- 1. Check direct sponsor relationships vs referral_trees Level 1
SELECT 'Direct Sponsor Check' as check_type;
SELECT 
    u.id, u.name, u.username, u.sponsor_id,
    rt.level as referral_tree_level
FROM users u
LEFT JOIN referral_trees rt ON u.id = rt.descendant_id AND rt.ancestor_id = 2
WHERE u.sponsor_id = 2 AND u.blocked = 0 AND u.can_login = 1
ORDER BY u.id;

-- 2. Check Level 6 and 7 users specifically
SELECT 'Level 6 vs 7 Analysis' as check_type;
SELECT 
    'Level 6' as level,
    rt.descendant_id as user_id,
    u.name,
    u.sponsor_id,
    sponsor.name as sponsor_name
FROM referral_trees rt
JOIN users u ON rt.descendant_id = u.id
LEFT JOIN users sponsor ON u.sponsor_id = sponsor.id
WHERE rt.ancestor_id = 2 AND rt.level = 6

UNION ALL

SELECT 
    'Level 7' as level,
    rt.descendant_id as user_id,
    u.name,
    u.sponsor_id,
    sponsor.name as sponsor_name  
FROM referral_trees rt
JOIN users u ON rt.descendant_id = u.id
LEFT JOIN users sponsor ON u.sponsor_id = sponsor.id
WHERE rt.ancestor_id = 2 AND rt.level = 7
ORDER BY level, user_id;

-- 3. Check if Level 7 users should actually be Level 6
SELECT 'Trace Path Analysis' as check_type;
WITH RECURSIVE user_path AS (
    -- Start with Level 7 users
    SELECT 
        rt.descendant_id as user_id,
        u.name,
        u.sponsor_id,
        1 as path_level
    FROM referral_trees rt
    JOIN users u ON rt.descendant_id = u.id  
    WHERE rt.ancestor_id = 2 AND rt.level = 7
    LIMIT 3  -- Just check first 3 for debugging
    
    UNION ALL
    
    -- Trace back to user 2
    SELECT 
        up.sponsor_id as user_id,
        parent.name,
        parent.sponsor_id,
        up.path_level + 1
    FROM user_path up
    JOIN users parent ON up.sponsor_id = parent.id
    WHERE up.sponsor_id != 2 AND up.path_level < 10
)
SELECT 
    user_id,
    name,
    sponsor_id,
    path_level,
    'Path from Level 7 user back to user 2' as description
FROM user_path
ORDER BY user_id, path_level;