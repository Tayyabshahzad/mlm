# Temporary Reward Review Page - Instructions

## Purpose
This temporary administrative page helps identify users who may have received rewards incorrectly, allowing you to review and reverse those rewards if needed.

## How to Access
1. Log in as an admin user
2. Navigate to the admin sidebar menu
3. Look for "Reward Review (Temp)" under the reward management section
4. Click to access the reward review dashboard

## Features

### Main Dashboard (`/admin/reward-review`)
- **Statistics Overview**: Shows statistics for each reward level including:
  - Total users who received the reward
  - Current number of eligible users  
  - Potential over-rewarded users (highlighted in orange/red)

- **User List**: Displays all users who have received rewards with:
  - User information (ID, name, email)
  - Reward levels achieved and amounts
  - Team analysis for highest reward level
  - Status indicator (⚠️ Review Needed or ✓ Looks Good)

- **Filters & Search**:
  - Filter by reward level
  - Search by user name, email, or ID
  - Export all data to CSV format

### User Detail View (`/admin/reward-review/{user}`)
- **User Information**: Basic user details and account status
- **Reward & Team Analysis**: Table showing for each level:
  - Required team count vs. current team count
  - Whether user has the reward
  - Status (Correct, Over-rewarded, Missing Reward, Not Eligible)

- **Current Rewards**: List of rewards currently in user's wallet
- **Pending Rewards**: Any pending reward approvals
- **Issues Detected**: Automatically identified problems such as:
  - Users with insufficient team size for their rewards
  - Users who skipped reward levels
  - Invalid reward levels

### Reward Reversal
- **Reverse Button**: Available on user detail page for each reward
- **Confirmation Modal**: Requires reason for reversal
- **Audit Trail**: Records who reversed the reward and why

## Identifying Problem Users

### Look for these indicators:
1. **Orange/Red statistics cards** - Indicate more users have rewards than should be eligible
2. **⚠️ Review Needed status** - Users whose highest reward level exceeds their current team size
3. **Red highlighted rows** in team analysis - Users with rewards but insufficient team
4. **Issues Detected section** - Automatically flagged problems

### Common Issues:
- **Over-rewarded**: User has reward but team size dropped below requirement
- **Skipped Levels**: User has higher level rewards without lower level ones
- **Invalid Levels**: Rewards for non-existent levels

## How to Reverse Incorrect Rewards
1. Go to the user's detail page
2. Find the incorrect reward in "Current Rewards" section  
3. Click "Reverse Reward" button
4. Enter a reason for the reversal
5. Confirm the action
6. The reward will be removed from user's wallet and logged

## Data Export
- Use the "Export CSV" button on the main page
- Contains all reward assignments with timestamps
- Useful for analysis in Excel or other tools

## Important Notes
- This is a **temporary page** for cleanup purposes
- All reversal actions are logged and auditable
- Users will see reward removals in their wallet immediately
- Consider backing up data before making bulk changes
- The page identifies potential issues but requires manual review

## Cleanup Process Recommendation
1. Review the statistics dashboard to identify problematic levels
2. Focus on users marked "Review Needed" 
3. Check each user's team count vs. reward requirements
4. Reverse rewards for users who clearly don't meet requirements
5. Document patterns of incorrect assignments for process improvement

## Technical Details
- **Controller**: `App\Http\Controllers\Admin\RewardReviewController`
- **Views**: `resources/views/admin/reward-review/`
- **Routes**: All routes prefixed with `/admin/reward-review`
- **Database**: Queries `wallets`, `users`, `pending_rewards`, and `referral_trees` tables

## Removal
Once the reward cleanup is complete, you can remove this temporary functionality by:
1. Deleting the controller file
2. Removing the routes from `web.php`
3. Deleting the view files
4. Removing the navigation link from the admin sidebar
5. Deleting this instruction file