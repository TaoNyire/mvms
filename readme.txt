// Chronological API Implementation Plan for MVMS (Malawi Volunteer Management System)

/**
 * USER ROLES AND DASHBOARD CONTENTS
 */

/**
 * 1. Volunteer
 * - Registers and creates profile with skills and availability
 * - Views personalized dashboard with:
 *     - Matched opportunities
 *     - Application history & statuses
 *     - Task assignments & progress
 *     - Feedback from organizations
 */

/**
 * 2. Organization
 * - Registers and creates organization profile with mission, vision, and areas of focus
 * - Organization dashboard includes:
 *     - List of posted opportunities (CRUD)
 *     - Volunteer applications per opportunity
 *     - Volunteer management tools (assign, reject, feedback)
 *     - Organization profile management
 *     - Application reports (optional export)
 */

/**
 * 3. Admin
 * - Has full access to all data
 * - Admin dashboard includes:
 *     - System user management (view users, assign roles)
 *     - Organization verification and activation
 *     - System-wide statistics and analytics
 *     - Feedback moderation
 *     - Skill management
 *     - Report generation (CSV/PDF)
 */

/**
 * STAGE 1: AUTHENTICATION & ROLE SETUP
 */
1. Create Auth system (Laravel Breeze / Sanctum for SPA support)
2. Register & Login endpoints (with validation)
3. Assign roles on registration (volunteer, organization)
4. Middleware for role-based access control

/**
 * STAGE 2: USER PROFILES
 */
5. Volunteer Profile API:
   - Store/Update Bio, Skills, Location, Region, District
   - Link profile to user_id
   - Fetch authenticated volunteer's profile

6. Organization Profile API:
   - Store/Update org_name, mission, vision, contact info
   - Link profile to user_id
   - Fetch authenticated organization's profile

/**
 * STAGE 3: SKILLS MANAGEMENT
 */
7. Skill Management:
   - Admin-only: Create, list, update, delete skill tags
   - Volunteer: Attach skills to profile
   - Opportunity: Assign required skills

/**
 * STAGE 4: OPPORTUNITIES
 */
8. Organization creates opportunity:
   - title, description, required_skills, dates, location
   - Authenticated org can post, update, delete their own

9. Public/volunteer APIs:
   - List all opportunities
   - Filter by skill, location, org, date

10. Matching Engine (backend logic only):
   - When volunteer logs in or views dashboard, match opportunities by skills and location

/**
 * STAGE 5: APPLICATION SYSTEM
 */
11. Volunteer applies to an opportunity
12. Prevent duplicate applications
13. Organization views applications to their postings
14. Organization can accept/reject (update status + responded_at)
15. Volunteer receives status update (via notification/email)

/**
 * STAGE 6: TASK TRACKING
 */
16. Task Status endpoint:
   - Volunteer marks task as In Progress / Completed / Quit
   - Org and Admin view volunteer task progress

17. Feedback System:
   - Organization gives feedback to volunteer (rating/comments)

/**
 * STAGE 7: ADMIN DASHBOARD
 */
18. Admin APIs:
   - View all users, roles, organizations
   - Approve/reject organizations
   - View all opportunities, applications, and feedback
   - Export reports (CSV/PDF optional)

/**
 * STAGE 8: NOTIFICATIONS
 */
19. Integrate Laravel Mail:
   - Welcome, verification, password reset
   - Application status updates
20. Integrate Twilio (optional):
   - SMS alerts on acceptance, rejection, or assignment

/**
 * STAGE 9: SECURITY & VALIDATION
 */
21. FormRequest validation for all endpoints
22. Policies/Gates for resource-level access control
23. Rate limiting, auth guards, CSRF for web routes

/**
 * STAGE 10: FINAL POLISHING
 */
24. API Documentation (Laravel Scribe or Swagger)
25. Testing (Pest or PHPUnit)
26. Deployment on production-ready server



//
i want org to view the volunteers their currently working with, the task assigned to them and also view rescently employeed volunteers. 
 php artisan schedule:work